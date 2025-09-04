<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\Classifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClassifyTicket implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $ticketId) {}

    public function handle(Classifier $clf): void {
        $t = Ticket::find($this->ticketId);
        if (!$t) return;

        $t->update(['classification_status' => 'running']);

        try {
            $res = $clf->predict(($t->subject ?? '') . ' ' . ($t->description ?? ''));
            $t->ai_category = $res['category'];
            $t->ai_confidence = $res['confidence'];
            $t->classification_status = 'done';
            $t->classified_at = now();
            $t->save();
        } catch (\Throwable $e) {
            $t->update(['classification_status' => 'failed']);
            throw $e;
        }
    }
}
