<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\Contracts\TicketClassifier;

class OpenAIClassifier implements TicketClassifier
{
    public function predict(string $text): array
    {
        $apiKey = (string) config('services.openai.key');
        $model  = (string) config('services.openai.model');

        if ($apiKey === '' || $model === '') {
            return ['category' => 'Other', 'confidence' => 0.3, 'explanation' => 'OpenAI not configured'];
        }

        $schema = [
            'name'   => 'TicketClassification',
            'strict' => true,
            'schema' => [
                'type'                 => 'object',
                'additionalProperties' => false,
                'properties'           => [
                    'category'    => ['type' => 'string', 'enum' => ['Billing','Bug','Access','Feature Request','Outage','Other']],
                    'confidence'  => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                    'explanation' => ['type' => 'string'],
                ],
                'required' => ['category','confidence'],
            ],
        ];

        $system = <<<SYS
You are a help-desk triage assistant. Classify each ticket into exactly one of:
Billing, Bug, Access, Feature Request, Outage, Other.
Return ONLY a JSON object that matches the provided schema. Confidence is in [0,1].
SYS;

        $payload = [
            'model'  => $model,
            'input'  => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => "Ticket text:\n".$text],
            ],
            'response_format' => [
                'type'        => 'json_schema',
                'json_schema' => $schema,
            ],
            'temperature' => 0.2,
        ];

        $res = Http::timeout(12)
            ->retry(2, 250)
            ->withToken($apiKey)
            ->post('https://api.openai.com/v1/responses', $payload)
            ->throw()
            ->json();

        // Prefer parsed structured output
        $parsed = $res['output_parsed'][0] ?? null;

        // Fallback to text content if needed
        if (!$parsed) {
            $txt = data_get($res, 'output.0.content.0.text');
            if (is_string($txt)) {
                $maybe = json_decode($txt, true);
                if (is_array($maybe)) $parsed = $maybe;
            }
        }

        if (!is_array($parsed)) {
            return ['category' => 'Other', 'confidence' => 0.3, 'explanation' => 'Parse fallback'];
        }

        $category   = is_string($parsed['category'] ?? null) ? $parsed['category'] : 'Other';
        $confidence = (float) ($parsed['confidence'] ?? 0.5);
        $confidence = max(0.0, min(1.0, $confidence));
        $explain    = isset($parsed['explanation']) && is_string($parsed['explanation']) ? $parsed['explanation'] : null;

        return ['category' => $category, 'confidence' => $confidence, 'explanation' => $explain];
    }
}