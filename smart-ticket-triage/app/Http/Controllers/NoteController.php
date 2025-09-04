<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Ticket;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $r, Ticket $ticket) {
        $data = $r->validate(['body'=>'required|string','author'=>'nullable|string|max:60']);
        return $ticket->notes()->create([
            'body'=>$data['body'],
            'author'=>$data['author'] ?? 'agent',
        ]);
    }

    public function update(Request $r, Note $note) {
        $data = $r->validate(['body'=>'required|string']);
        $note->update($data);
        return $note;
    }

    public function destroy(Note $note) {
        $note->delete();
        return response()->noContent();
    }
}
