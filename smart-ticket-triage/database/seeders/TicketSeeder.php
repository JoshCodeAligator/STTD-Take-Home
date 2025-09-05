<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        Ticket::factory()->count(30)->create()->each(function (Ticket $t) {
            if (rand(0,1)) {
                $t->notes()->create([
                    'author' => 'agent',
                    'body'   => 'Initial triage: '.fake()->sentence(),
                ]);
            }
        });
    }
}