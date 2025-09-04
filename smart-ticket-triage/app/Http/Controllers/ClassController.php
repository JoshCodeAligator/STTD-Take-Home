<?php

namespace App\Http\Controllers;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;

class ClassController extends Controller
{
    public function enqueue(Ticket $ticket) {
        $ticket->update(['classification_status'=>'queued']);
        dispatch(new ClassifyTicket($ticket->id));
        return response()->json(['queued'=>true], 202);
    }
}
