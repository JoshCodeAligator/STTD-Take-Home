<?php

namespace App\Http\Controllers;

use App\Jobs\ClassifyTicket;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $r) {
        return Ticket::withCount('notes')
            ->search($r->q)
            ->status($r->status)
            ->category($r->category)
            ->latest()
            ->paginate(20);
    }

    public function store(Request $r) {
        $data = $r->validate([
            'subject'=>'required|string|max:200',
            'description'=>'required|string',
            'requester_email'=>'required|email',
            'status'=>'in:new,open,closed',
            'classify'=>'sometimes|boolean'
        ]);
        $t = Ticket::create([
            'subject'=>$data['subject'],
            'description'=>$data['description'],
            'requester_email'=>$data['requester_email'],
            'status'=>$data['status'] ?? 'new'
        ]);
        if (!empty($data['classify'])) {
            $t->update(['classification_status'=>'queued']);
            dispatch(new ClassifyTicket($t->id));
        }
        return response()->json($t->refresh(), 201);
    }

    public function show(Ticket $ticket) {
        return $ticket->load('notes');
    }

    public function update(Request $r, Ticket $ticket) {
        $data = $r->validate([
            'status'=>'nullable|in:new,open,closed',
            'override_category'=>'nullable|string|max:100',
        ]);
        $ticket->update($data);
        return $ticket->refresh()->load('notes');
    }
}
