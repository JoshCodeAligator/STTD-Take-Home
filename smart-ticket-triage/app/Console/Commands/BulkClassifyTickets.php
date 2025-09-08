<?php
declare(strict_types=1);
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Jobs\ClassifyTicket;

class BulkClassifyTickets extends Command {
    protected $signature = 'tickets:bulk-classify {--status=new}';
    protected $description = 'Dispatch classify jobs for tickets (optionally filter by status)';
    public function handle(): int {
        $q = Ticket::query();
        if ($st = $this->option('status')) $q->where('status', $st);
        $count = 0;
        $q->orderBy('created_at')->chunk(100, function($batch) use (&$count) {
            foreach ($batch as $t) { dispatch(new ClassifyTicket((string)$t->id)); $count++; }
        });
        $this->info("Dispatched $count classify jobs.");
        return self::SUCCESS;
    }
}