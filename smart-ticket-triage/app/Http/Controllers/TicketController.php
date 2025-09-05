<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) max(5, min(100, (int) $request->input('per_page', 10)));
        $q       = $request->input('q');
        $status  = $request->input('status');
        $cat     = $request->input('category');

        $paginator = Ticket::withCount('notes')
            ->search($q)
            ->status($status)
            ->category($cat)
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject'         => ['required', 'string', 'max:200'],
            'body'            => ['required', 'string'],
            'requester_email' => ['nullable', 'email'],
        ]);

        $ticket = Ticket::create($data + ['status' => 'new']);

        return response()->json($ticket->fresh()->loadCount('notes'), 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        return response()->json($ticket->load('notes'));
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validate([
            'status'            => ['nullable', 'in:new,open,closed'],
            'override_category' => ['nullable', 'string', 'max:100'],
            'note'              => ['nullable', 'string'],
        ]);

        $update = $data; unset($update['note']);
        if (!empty($update)) $ticket->update($update);

        if (!empty($data['note'])) {
            $ticket->notes()->create([
                'body'   => $data['note'],
                'author' => 'agent',
            ]);
        }

        return response()->json($ticket->refresh()->load('notes'));
    }
}