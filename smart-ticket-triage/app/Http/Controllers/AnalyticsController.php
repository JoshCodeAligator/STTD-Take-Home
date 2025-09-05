<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function summary(): JsonResponse
    {
        $byCategory = Ticket::select(
                DB::raw("COALESCE(override_category, ai_category, 'Uncategorized') as category"),
                DB::raw('count(*) as cnt')
            )->groupBy('category')->pluck('cnt','category');

        $byStatus = Ticket::select('status', DB::raw('count(*) as cnt'))
            ->groupBy('status')->pluck('cnt','status');

        $overrides = Ticket::whereNotNull('override_category')->count();

        $avgSecs = Ticket::whereNotNull('classified_at')
            ->selectRaw("AVG(strftime('%s', classified_at) - strftime('%s', created_at)) as s")
            ->value('s') ?? 0;

        return response()->json([
            'byCategory' => $byCategory,
            'byStatus'   => $byStatus,
            'overrides'  => $overrides,
            'avgClassificationSeconds' => round((float)$avgSecs, 1),
        ]);
    }
}