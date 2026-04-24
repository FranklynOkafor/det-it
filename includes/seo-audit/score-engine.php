<?php

namespace DetIt\SeoAudit;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Calculate deterministic SEO score based on detected issues.
 */
class ScoreEngine
{
    /**
     * Calculate score and deductions.
     *
     * @param array $issues List of issue IDs (e.g., ['missing_meta_description', 'no_bullet_lists'])
     * @return array Structured array with score, total_deductions, breakdown, and issue counts
     */
    public static function calculate(array $issues): array
    {
        if (empty($issues)) {
            return [
                'score' => 100,
                'total_deductions' => 0,
                'breakdown' => [],
                'issue_counts' => [
                    'critical' => 0,
                    'medium' => 0,
                    'low' => 0
                ]
            ];
        }

        $issues = array_unique($issues);

        sort($issues);

        $registry = require __DIR__ . '/issue_registry.php';

        $deductions = 0;
        $breakdown = [];
        $counts = [
            'critical' => 0,
            'medium' => 0,
            'low' => 0
        ];

        // 4. Sum deductions based on issue weights
        foreach ($issues as $issue_id) {
            if (isset($registry[$issue_id])) {
                $issue_data = $registry[$issue_id];
                $weight = isset($issue_data['weight']) ? (int) $issue_data['weight'] : 0;
                $severity = $issue_data['severity'];

                $deductions += $weight;

                if (isset($counts[$severity])) {
                    $counts[$severity]++;
                }

                $breakdown[] = [
                    'issue' => $issue_id,
                    'severity' => $severity,
                    'deduction' => $weight,
                ];
            }
        }

        // 5. Calculate base score and clamp to 0
        $score = max(0, 100 - $deductions);

        return [
            'score' => $score,
            'total_deductions' => $deductions,
            'breakdown' => $breakdown,
            'issue_counts' => $counts
        ];
    }
}
