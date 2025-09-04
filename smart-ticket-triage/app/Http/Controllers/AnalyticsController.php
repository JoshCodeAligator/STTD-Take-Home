<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function summary() {
        $byCategory = Ticket::select(
                DB::raw("COALESCE(override_category, ai_category, 'Uncategorized') as category"),
                DB::raw('count(*) as cnt')
            )
            ->groupBy('category')
            ->pluck('cnt','category');

        $overrides = Ticket::whereNotNull('override_category')->count();

        $avgSecs = Ticket::whereNotNull('classified_at')
            ->select(DB::raw("(AVG((julianday(classified_at) - julianday(created_at)) * 86400)) as s"))
            ->value('s') ?? 0;

        $byStatus = Ticket::select('status', DB::raw('count(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt','status');

        return [
            'byCategory' => $byCategory,
            'overrides' => $overrides,
            'avgClassificationSeconds' => round((float)$avgSecs, 1),
            'byStatus' => $byStatus,
        ];
    }
}
