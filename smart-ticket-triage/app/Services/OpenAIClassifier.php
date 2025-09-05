<?php
declare(strict_types=1);

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Services\Contracts\TicketClassifier;

final class OpenAIClassifier implements TicketClassifier
{
    public function predict(string $text): array
    {
        $resp = OpenAI::responses()->create([
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'input' => [
                [
                    'role'    => 'system',
                    'content' =>
                        "You are a help-desk ticket classifier. ".
                        "Return ONLY strict JSON with keys: ".
                        "category (string), explanation (string), confidence (number 0..1). ".
                        "Valid categories: Billing, Bug, Access, Feature Request, Outage, Other. ".
                        "No prose, no markdown, no backticks — JSON only."
                ],
                ['role' => 'user', 'content' => (string) $text],
            ],
            'temperature' => 0.0,
        ]);

        $raw  = (string) ($resp->outputText ?? '{}');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            return ['category' => 'Other', 'confidence' => 0.3, 'explanation' => 'Parse fallback'];
        }

        $cat  = is_string($data['category'] ?? null) ? $data['category'] : 'Other';
        $conf = max(0.0, min(1.0, (float) ($data['confidence'] ?? 0.5)));
        $exp  = isset($data['explanation']) && is_string($data['explanation']) ? $data['explanation'] : null;

        return ['category' => $cat, 'confidence' => $conf, 'explanation' => $exp];
    }
}