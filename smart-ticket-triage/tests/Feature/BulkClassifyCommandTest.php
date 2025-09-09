<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ClassifyTicket;
use App\Models\Ticket;

final class BulkClassifyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_classify_command_dispatches_a_job_per_ticket(): void
    {
        Queue::fake();

        // Prepare some tickets
        Ticket::factory()->count(5)->create(['status' => 'new']);

        $this->artisan('tickets:bulk-classify')
             ->assertExitCode(0);

        Queue::assertPushed(ClassifyTicket::class, 5);
    }
}