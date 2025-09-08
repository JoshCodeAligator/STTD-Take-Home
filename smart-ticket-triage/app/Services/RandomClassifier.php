<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\Contracts\TicketClassifier;

/**
 * Random fallback classifier used when OPENAI_CLASSIFY_ENABLED=false.
 *
 * Requirements satisfied:
 * - Ignores input text completely.
 * - Returns a random category from a fixed set.
 * - Returns a dummy explanation and a confidence in [0.30, 0.90].
 */
final class RandomClassifier implements TicketClassifier
{
    /**
     * Fixed category set used for random selection.
     * @var list<string>
     */
    private const CATEGORIES = ['Billing','Bug','Access','Feature Request','Outage','Other'];

    /**
     * Predict a random classification result.
     *
     * @param string $text Ignored intentionally when OpenAI is disabled.
     * @return array{category:?string, explanation:?string, confidence:float}
     */
    public function predict(string $text): array
    {
        $category   = self::CATEGORIES[random_int(0, count(self::CATEGORIES) - 1)];
        $confidence = round(random_int(30, 90) / 100, 2);

        return [
            'category'    => $category,
            'explanation' => 'Dummy result (OpenAI disabled): random category for testing.',
            'confidence'  => $confidence,
        ];
    }
}