<?php
namespace App\Services;

use App\Services\Contracts\TicketClassifier;

class RulesClassifier implements TicketClassifier
{
    public function predict(string $text): array
    {
        // Split subject/description if you want extra weighting
        [$subject, $body] = $this->split($text);

        $rules     = config('support.rules');
        $priority  = config('support.priority', []);
        $wSubject  = (float) config('support.subject_weight', 2.0);
        $minScore  = (int) config('support.min_score_for_category', 2);

        $sNorm = $this->normalize($subject);
        $bNorm = $this->normalize($body);

        $scores = [];
        foreach ($rules as $cat => $keys) {
            $score = 0;
            foreach ($keys as $k) {
                $isRegex = str_starts_with($k, '/') && str_ends_with($k, '/');
                if ($isRegex) {
                    $pattern = $k;
                    $score += preg_match($pattern.'i', $sNorm) ? $wSubject : 0;
                    $score += preg_match($pattern.'i', $bNorm) ? 1 : 0;
                } else {
                    if (str_contains($sNorm, $k)) $score += $wSubject;
                    if (str_contains($bNorm, $k)) $score += 1;
                }
            }
            $scores[$cat] = $score;
        }

        // tie-break: highest score, then by configured priority
        arsort($scores, SORT_NUMERIC);
        $top   = array_key_first($scores) ?? 'Other';
        $topSc = (float) ($scores[$top] ?? 0.0);

        // convert to crude confidence
        $sum = array_sum($scores);
        $confidence = $sum > 0 ? ($topSc / max(1e-6, $sum)) : 0.0;
        $confidence = max(0.0, min(1.0, $confidence));

        // category thresholding
        if ($topSc < $minScore) {
            return ['category' => 'Uncategorized', 'confidence' => round($confidence, 2)];
        }

        // ensure deterministic priority on ties
        $ties = array_keys($scores, $topSc, true);
        if (count($ties) > 1) {
            foreach ($priority as $p) {
                if (in_array($p, $ties, true)) { $top = $p; break; }
            }
        }

        return ['category' => $top, 'confidence' => round($confidence, 2)];
    }

    private function split(string $t): array
    {
        // If you store subject/description separately, pass them in and skip this.
        // Heuristic: first line as subject, rest as body.
        $t = trim($t);
        $parts = preg_split("/\r\n|\n|\r/", $t, 2);
        return [$parts[0] ?? $t, $parts[1] ?? $t];
    }

    private function normalize(string $t): string
    {
        $t = mb_strtolower($t);
        // strip punctuation
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t) ?? $t;
        $t = preg_replace('/\s+/', ' ', $t) ?? $t;

        // crude negation handling: drop billing-related hits if near a negation keyword
        // (Extend per category if you hit false positives.)
        $neg = config('support.negations', []);
        foreach ($neg as $n) {
            // Example: "not a refund" – prevent counting 'refund' by replacing with placeholder
            $t = preg_replace('/'.preg_quote($n, '/').'(refund|charge|billing)/', ' NEG_$1', $t) ?? $t;
        }
        return trim($t);
    }
}