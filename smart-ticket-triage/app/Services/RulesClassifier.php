<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\Contracts\TicketClassifier;

/**
 * Deterministic rules-based fallback classifier.
 * - Reads keywords/weights/priority from config/support.php
 * - Weights subject matches higher than body
 * - Returns: category, explanation, confidence (0..1)
 */
class RulesClassifier implements TicketClassifier
{
    /**
     * @return array{category:?string, explanation:?string, confidence:float}
     */
    public function predict(string $text): array
    {
        [$subject, $body] = $this->split($text);

        /** @var array<string,string[]> $rules */
        $rules    = (array) config('support.rules', []);
        /** @var string[] $priority */
        $priority = (array) config('support.priority', []);
        $wSubject = (float) config('support.subject_weight', 2.0);
        $minScore = (float) config('support.min_score_for_category', 2.0);

        $sNorm = $this->normalize($subject);
        $bNorm = $this->normalize($body);

        $scores = [];
        $matches = []; // keep the keywords we hit per category for explanation

        foreach ($rules as $cat => $keys) {
            $score = 0.0;
            foreach ($keys as $k) {
                $isRegex = str_starts_with($k, '/') && str_ends_with($k, '/');
                $hitSub = false; $hitBody = false;
                if ($isRegex) {
                    $pattern = $k.'i';
                    $hitSub  = (bool) preg_match($pattern, $sNorm);
                    $hitBody = (bool) preg_match($pattern, $bNorm);
                } else {
                    $hitSub  = str_contains($sNorm, $k);
                    $hitBody = str_contains($bNorm, $k);
                }
                if ($hitSub) { $score += $wSubject; $matches[$cat][] = $k; }
                if ($hitBody) { $score += 1.0; $matches[$cat][] = $k; }
            }
            $scores[$cat] = $score;
        }

        arsort($scores, SORT_NUMERIC);
        $top   = array_key_first($scores) ?? 'Other';
        $topSc = (float) ($scores[$top] ?? 0.0);

        // deterministic tie-breaker based on configured priority
        $ties = array_keys($scores, $topSc, true);
        if (count($ties) > 1) {
            foreach ($priority as $p) {
                if (in_array($p, $ties, true)) { $top = $p; break; }
            }
        }

        // crude confidence: top / sum, clipped to [0,1]
        $sum = array_sum($scores) ?: 0.0;
        $confidence = $sum > 0 ? ($topSc / max(1e-6, $sum)) : 0.0;
        $confidence = max(0.0, min(1.0, $confidence));

        // Build explanation text
        $hitList = array_unique($matches[$top] ?? []);
        if ($topSc < $minScore) {
            return [
                'category'    => 'Other',
                'explanation' => 'Rules fallback: insufficient evidence for a specific category; defaulted to Other.',
                'confidence'  => round($confidence, 2),
            ];
        }

        $explanation = $this->buildExplanation($top, $hitList, $topSc, $confidence, $wSubject);

        return [
            'category'    => $top,
            'explanation' => $explanation,
            'confidence'  => round($confidence, 2),
        ];
    }

    private function buildExplanation(string $cat, array $hits, float $score, float $conf, float $wSubject): string
    {
        $hits = array_values(array_unique($hits));
        $top3 = array_slice($hits, 0, 3);
        $hitText = $top3 ? ('keywords: '.implode(', ', $top3)) : 'no specific keywords';
        $detail = sprintf('score=%.2f (subject weight=%.1f), confidence=%.2f', $score, $wSubject, $conf);
        return sprintf('Rules fallback → %s (%s; %s).', $cat, $hitText, $detail);
    }

    private function split(string $t): array
    {
        $t = trim($t);
        $parts = preg_split("/\r\n|\n|\r/", $t, 2) ?: [];
        return [$parts[0] ?? $t, $parts[1] ?? $t];
    }

    private function normalize(string $t): string
    {
        $t = mb_strtolower($t);
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t) ?? $t;
        $t = preg_replace('/\s+/', ' ', $t) ?? $t;

        // Optional negation handling via config('support.negations')
        $neg = (array) config('support.negations', []);
        foreach ($neg as $n) {
            $t = preg_replace('/'.preg_quote($n, '/').'(refund|charge|billing)/', ' NEG_$1', $t) ?? $t;
        }
        return trim($t);
    }
}