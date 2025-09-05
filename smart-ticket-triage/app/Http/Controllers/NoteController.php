<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validate([
            'body'   => ['required', 'string'],
            'author' => ['nullable', 'string', 'max:100'],
        ]);

        $note = $ticket->notes()->create($data + ['author' => $data['author'] ?? 'agent']);
        return response()->json($note, 201);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $data = $request->validate(['body' => ['required','string']]);
        $note->update($data);
        return response()->json($note->refresh());
    }

    public function destroy(Note $note): Response
    {
        $note->delete();
        return response()->noContent();
    }
}