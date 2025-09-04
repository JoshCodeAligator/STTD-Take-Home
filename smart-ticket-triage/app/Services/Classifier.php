<?php

namespace App\Services;

class Classifier
{
    /** @return array{category:string, confidence:float} */
    public function predict(string $text): array {
        $text = mb_strtolower($text);
        $rules = [
            'Billing' => ['invoice','refund','charge','payment','billing','credit'],
            'Bug' => ['error','crash','broken','bug','fail','stacktrace','exception'],
            'Access' => ['login','password','reset','permission','2fa','mfa','locked'],
            'Feature Request' => ['feature','request','enhancement','improve','roadmap'],
            'Outage' => ['down','unavailable','outage','latency','timeout'],
            'Other' => []
        ];
        $scores = [];
        foreach ($rules as $cat => $keys) {
            $s = 0;
            foreach ($keys as $k) if (str_contains($text, $k)) $s++;
            $scores[$cat] = $s;
        }
        arsort($scores);
        $top = array_key_first($scores);
        $conf = max(0.3, min(0.99, ($scores[$top] ?? 0) / max(1, array_sum($scores))));
        return ['category' => $top, 'confidence' => round($conf, 2)];
    }
}
