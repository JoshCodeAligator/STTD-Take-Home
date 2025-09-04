<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Contracts\TicketClassifier;

class ClassifyTicket implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $ticketId) {}

    public function handle(TicketClassifier $clf): void
    {
        $t = Ticket::find($this->ticketId);
        if (!$t) return;

        $t->update(['classification_status' => 'running']);

        try {
            $res = $clf->predict(trim(($t->subject ?? '').' '.($t->description ?? '')));
            $t->ai_category           = $res['category']    ?? null;
            $t->ai_confidence         = $res['confidence']  ?? null;
           
            // $t->ai_explanation     = $res['explanation'] ?? null;
            $t->classification_status = 'done';
            $t->classified_at         = now();
            $t->save();
        } catch (\Throwable $e) {
            $t->update(['classification_status' => 'failed']);
            throw $e;
        }
    }
}