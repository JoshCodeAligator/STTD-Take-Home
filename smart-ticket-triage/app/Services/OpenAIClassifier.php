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

        // This is ONLY the JSON Schema (no outer wrapper). Keep it simple & strict.
        $jsonSchema = [
            'type'                 => 'object',
            'additionalProperties' => false,
            'properties'           => [
                'category'   => ['type' => 'string', 'enum' => ['Billing','Bug','Access','Feature Request','Outage','Other']],
                'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                'explanation'=> ['type' => 'string'],
            ],
            
            'required' => ['category', 'confidence', 'explanation'],
        ];

        $system = <<<SYS
You are a help-desk triage assistant. Classify each ticket into exactly one of:
Billing, Bug, Access, Feature Request, Outage, Other.
Return ONLY a JSON object that matches the provided schema. Confidence is in [0,1].
SYS;

        // Responses API: structured outputs go under text.format with required name + schema
        $payload = [
            'model' => $model,
            'input' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => "Ticket text:\n".$text],
            ],
            'text' => [
                'format' => [
                    'type'   => 'json_schema',
                    'name'   => 'TicketClassification', // required by Responses API
                    'schema' => $jsonSchema,            // <-- required; this fixes “Missing ... text.format.schema”
                    'strict' => true,                   // keep strict so enums/required are enforced
                ],
            ],
            'temperature' => 0.2,
        ];

        try {
            $res = Http::timeout(20)
                ->retry(2, 300)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/responses', $payload)
                ->throw()
                ->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('OpenAI HTTP error', [
                'status' => optional($e->response)->status(),
                'body'   => optional($e->response)->body(),
                'msg'    => $e->getMessage(),
            ]);
            throw $e; // your job’s fallback will handle this if you added it
        }

        // Prefer parsed output (varies slightly across client/server)
        $parsed = $res['output_parsed'][0] ?? null;
        if (!$parsed) {
            $parsed = $res['output'][0]['parsed'] ?? null;
        }
        if (!$parsed) {
            $txt = data_get($res, 'output.0.content.0.text');
            if (is_string($txt)) {
                $maybe = json_decode($txt, true);
                if (is_array($maybe)) $parsed = $maybe;
            }
        }

        if (!is_array($parsed)) {
            \Log::warning('OpenAI parse fallback used', ['response_keys' => array_keys($res ?? [])]);
            return ['category' => 'Other', 'confidence' => 0.3, 'explanation' => 'Parse fallback'];
        }

        $category   = is_string($parsed['category'] ?? null) ? $parsed['category'] : 'Other';
        $confidence = (float) ($parsed['confidence'] ?? 0.5);
        $confidence = max(0.0, min(1.0, $confidence));
        $explain    = isset($parsed['explanation']) && is_string($parsed['explanation']) ? $parsed['explanation'] : null;

        return ['category' => $category, 'confidence' => $confidence, 'explanation' => $explain];
    }
}