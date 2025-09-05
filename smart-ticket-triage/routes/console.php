<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Ticket;
use App\Jobs\ClassifyTicket;

Artisan::command('tickets:bulk-classify', function () {
    $count = 0;

    Ticket::whereNotIn('classification_status', ['running', 'done'])
        ->orderByDesc('created_at')
        ->chunk(100, function ($chunk) use (&$count) {
            foreach ($chunk as $t) {
                $t->update(['classification_status' => 'queued']);
                dispatch(new ClassifyTicket((string) $t->id));
                $count++;
            }
        });

    $this->info("Queued $count tickets for classification.");
})->purpose('Queue classification for all tickets not yet processed');
