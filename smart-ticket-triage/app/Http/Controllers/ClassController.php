<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Jobs\ClassifyTicket;
use Illuminate\Http\JsonResponse;

class ClassController extends Controller
{
    public function enqueue(Ticket $ticket): JsonResponse
    {
        if ($ticket->classification_status === 'running') {
            return response()->json(['status' => 'already_running'], 202);
        }
        $ticket->update(['classification_status' => 'queued']);
        dispatch(new ClassifyTicket((string) $ticket->id));
        return response()->json(['status' => 'queued']);
    }
}