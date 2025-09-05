<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;
use App\Services\Contracts\TicketClassifier;

final class RateLimitedClassifier implements TicketClassifier
{
    public function __construct(private TicketClassifier $inner) {}

    public function predict(string $text): array
    {
        $key   = 'openai:minute';
        $max   = (int) env('OPENAI_RPM', 60);
        $decay = 60;

        $allowed = RateLimiter::attempt($key, $max, static function(){}, $decay);

        if (!$allowed) {
            return ['category' => 'Other', 'confidence' => 0.0, 'explanation' => 'Rate limited'];
        }
        return $this->inner->predict($text);
    }
}