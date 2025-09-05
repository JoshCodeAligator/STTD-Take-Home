<?php
declare(strict_types=1);

namespace App\Services\Contracts;

interface TicketClassifier
{
    /** @return array{category:string, confidence:float, explanation:?string} */
    public function predict(string $text): array;
}