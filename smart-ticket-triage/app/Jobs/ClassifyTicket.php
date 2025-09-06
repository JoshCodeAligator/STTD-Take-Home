<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Contracts\TicketClassifier;

/**
 * Queued classifier job. Preserves manual override; still updates explanation & confidence.  [oai_citation:4‡AI-Skills-Assessment-Test.pdf](file-service://file-P76HTC3Tq4SCr2zRzaDabp)
 */
final class ClassifyTicket implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $ticketId) {} // ULID

    public function handle(TicketClassifier $clf): void
    {
        $t = Ticket::findOrFail($this->ticketId);
        $t->update(['classification_status' => 'running']);
        try {
            $text = trim(($t->subject ?? '') . ' ' . ($t->body ?? ''));
            $res  = $clf->predict($text);

            // Update ONLY AI fields; never clobber override_category
            $t->ai_category           = $res['category'] ?? null;
            $t->ai_confidence         = isset($res['confidence']) ? (float) $res['confidence'] : null;
            $t->ai_explanation        = $res['explanation'] ?? 'Classification completed.';
            $t->classification_status = 'done';
            $t->classified_at         = now();
            $t->save();
        } catch (\Throwable $e) {
            $t->update([
                'classification_status' => 'failed',
                'ai_explanation'        => 'Classification failed: ' . $e->getMessage(),
            ]);
            \Log::warning('ClassifyTicket failed', [
                'ticket_id' => $this->ticketId,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}