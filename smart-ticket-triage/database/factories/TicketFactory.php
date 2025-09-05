<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'subject'         => $this->faker->sentence(),
            'body'            => $this->faker->paragraph(),
            'requester_email' => $this->faker->safeEmail(),
            'status'          => $this->faker->randomElement(['new','open','closed']),
        ];
    }
}