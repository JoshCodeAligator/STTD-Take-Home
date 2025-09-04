<?php

namespace App\Services\Contracts;

interface TicketClassifier
{
    /** @return array{category:string, confidence:float, explanation?:string|null} */
    public function predict(string $text): array;
}