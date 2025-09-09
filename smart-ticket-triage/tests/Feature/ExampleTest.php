<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ticket;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ClassifyTicket;

final class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run queued jobs inline for feature tests
        config()->set('queue.default', 'sync');
        // Ensure external calls are off so RandomClassifier is used
        putenv('OPENAI_CLASSIFY_ENABLED=false');
    }

    public function test_ticket_lifecycle_and_classification(): void
    {
        // 1) Create
        $payload = [
            'subject' => 'Sample subject',
            'body' => 'This is a sample ticket body',
            'requester_email' => 'user@example.com',
        ];

        $create = $this->postJson('/api/tickets', $payload)->assertCreated();
        $id = $create->json('id') ?? $create->json('data.id');
        $this->assertNotNull($id);

        // 2) List
        $this->getJson('/api/tickets?per_page=10&q=Sample&status=new')->assertOk();

        // 3) Show
        $this->getJson("/api/tickets/{$id}")->assertOk();

        // 4) Override category + add note
        $this->patchJson("/api/tickets/{$id}", [
            'override_category' => 'Bug',
            'note' => 'triage note',
        ])->assertOk();

        // 5) Classify (sync)
        $this->postJson("/api/tickets/{$id}/classify")->assertOk();

        // 6) Verify AI fields stored; respect override rule
        $ticket = Ticket::find($id);
        $this->assertNotNull($ticket);

        $category    = $ticket->category    ?? $ticket->ai_category;
        $confidence  = $ticket->confidence  ?? $ticket->ai_confidence;
        $explanation = $ticket->explanation ?? $ticket->ai_explanation;

        $this->assertNotNull($explanation);
        $this->assertIsFloat($confidence);
        $this->assertGreaterThanOrEqual(0.0, $confidence);
        $this->assertLessThanOrEqual(1.0, $confidence);

        if (! is_null($ticket->override_category)) {
            $this->assertSame($ticket->override_category, $category);
        }
    }

    public function test_stats_endpoint(): void
    {
        Ticket::factory()->count(3)->create();
        $this->getJson('/api/stats')->assertOk();
    }

    public function test_bulk_classify_command_dispatches_jobs(): void
    {
        Queue::fake();
        Ticket::factory()->count(5)->create(['status' => 'new']);

        $this->artisan('tickets:bulk-classify')->assertExitCode(0);
        Queue::assertPushed(ClassifyTicket::class, 5);
    }
}