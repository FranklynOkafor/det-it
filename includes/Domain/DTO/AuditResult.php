<?php

namespace DetIt\Domain\DTO;

use DetIt\Contracts\SerializableDTO;

/**
 * Class AuditResult
 *
 * Represents the result of auditing a product.
 */
class AuditResult implements SerializableDTO {
    /**
     * @var int The audited product's ID.
     */
    private int $product_id;

    /**
     * @var int The overall SEO score.
     */
    private int $score;

    /**
     * @var Issue[] Array of SEO issues found.
     */
    private array $issues;

    /**
     * @var array Generated suggestions for the product.
     */
    private array $generated_suggestions;

    /**
     * Constructor for AuditResult.
     *
     * @param int $product_id
     * @param int $score
     * @param Issue[] $issues
     * @param array $generated_suggestions
     */
    public function __construct(
        int $product_id = 0,
        int $score = 0,
        array $issues = [],
        array $generated_suggestions = []
    ) {
        $this->product_id = $product_id;
        $this->score = $score;
        $this->issues = [];
        
        foreach ($issues as $issue) {
            if ($issue instanceof Issue) {
                $this->addIssue($issue);
            }
        }
        
        $this->generated_suggestions = $generated_suggestions;
    }

    /**
     * Get the product ID.
     *
     * @return int
     */
    public function getProductId(): int {
        return $this->product_id;
    }

    /**
     * Get the overall score.
     *
     * @return int
     */
    public function getScore(): int {
        return $this->score;
    }

    /**
     * Get the list of issues.
     *
     * @return Issue[]
     */
    public function getIssues(): array {
        return $this->issues;
    }

    /**
     * Add a single issue to the results.
     *
     * @param Issue $issue
     * @return void
     */
    public function addIssue(Issue $issue): void {
        $this->issues[] = $issue;
    }

    /**
     * Get generated suggestions.
     *
     * @return array
     */
    public function getGeneratedSuggestions(): array {
        return $this->generated_suggestions;
    }

    /**
     * Convert the AuditResult to an array.
     *
     * @return array
     */
    public function toArray(): array {
        $issuesArray = [];
        foreach ($this->issues as $issue) {
            $issuesArray[] = $issue->toArray();
        }

        return [
            'product_id' => $this->product_id,
            'score' => $this->score,
            'issues' => $issuesArray,
            'generated_suggestions' => $this->generated_suggestions,
        ];
    }

    /**
     * Populate the AuditResult from an array.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data): void {
        $this->product_id = $data['product_id'] ?? 0;
        $this->score = $data['score'] ?? 0;
        $this->generated_suggestions = $data['generated_suggestions'] ?? [];
        
        $this->issues = [];
        if (isset($data['issues']) && is_array($data['issues'])) {
            foreach ($data['issues'] as $issueData) {
                $issue = new Issue();
                $issue->fromArray($issueData);
                $this->addIssue($issue);
            }
        }
    }
}
