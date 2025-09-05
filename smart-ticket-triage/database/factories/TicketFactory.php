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
    $templates = [
        // Billing
        ['subject' => 'Refund requested for wrong charge',
         'body' => 'I was overcharged on my invoice and need a refund for last month. Please fix the billing issue.',
         'status' => 'new'],
        // Bug
        ['subject' => 'App crashes with stack trace',
         'body' => 'The app throws an exception and crash error when I click Save. See attached error and stack trace.',
         'status' => 'open'],
        // Access
        ['subject' => 'Cannot login with 2FA',
         'body' => 'My account is locked out. Login fails after MFA. Please reset my password and access permissions.',
         'status' => 'new'],
        // Feature Request
        ['subject' => 'Feature request: dark mode and export',
         'body' => 'Could you add support for dark mode and a data export feature in the next release?',
         'status' => 'open'],
        // Outage
        ['subject' => 'Service down / 503 errors',
         'body' => 'The service is unreachable with 502/503 and timeout latency. We are experiencing an outage.',
         'status' => 'open'],
        // Other
        ['subject' => 'General question about plan',
         'body' => 'I have a question about my plan but not sure which category this falls under.',
         'status' => 'new'],
    ];

    $t = $this->faker->randomElement($templates);
    return [
        'subject'         => $t['subject'],
        'body'            => $t['body'],
        'requester_email' => $this->faker->safeEmail(),
        'status'          => $t['status'],
    ];
}
}