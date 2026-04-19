<?php

namespace DetIt\Domain\DTO;

use DetIt\Contracts\SerializableDTO;

/**
 * Class Issue
 *
 * Represents a single SEO issue found during a product audit.
 */
class Issue implements SerializableDTO {
    /**
     * @var string The unique identifier for the issue type.
     */
    private string $id;

    /**
     * @var string The severity level of the issue (e.g., 'high', 'medium', 'low').
     */
    private string $severity;

    /**
     * @var string A descriptive message explaining the issue.
     */
    private string $message;

    /**
     * @var string The field associated with the issue (e.g., 'product_title', 'meta_description').
     */
    private string $field;

    /**
     * @var mixed The current value of the field.
     */
    private $current_value;

    /**
     * @var mixed The recommended value for the field.
     */
    private $recommended_value;

    /**
     * Constructor for Issue.
     *
     * @param string $id
     * @param string $severity
     * @param string $message
     * @param string $field
     * @param mixed $current_value
     * @param mixed $recommended_value
     */
    public function __construct(
        string $id = '',
        string $severity = '',
        string $message = '',
        string $field = '',
        $current_value = null,
        $recommended_value = null
    ) {
        $this->id = $id;
        $this->severity = $severity;
        $this->message = $message;
        $this->field = $field;
        $this->current_value = $current_value;
        $this->recommended_value = $recommended_value;
    }

    /**
     * Get the issue identifier.
     *
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Get the issue severity.
     *
     * @return string
     */
    public function getSeverity(): string {
        return $this->severity;
    }

    /**
     * Get the issue message.
     *
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * Get the issue field.
     *
     * @return string
     */
    public function getField(): string {
        return $this->field;
    }

    /**
     * Get the current value.
     *
     * @return mixed
     */
    public function getCurrentValue() {
        return $this->current_value;
    }

    /**
     * Get the recommended value.
     *
     * @return mixed
     */
    public function getRecommendedValue() {
        return $this->recommended_value;
    }

    /**
     * Convert the Issue to an array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'severity' => $this->severity,
            'message' => $this->message,
            'field' => $this->field,
            'current_value' => $this->current_value,
            'recommended_value' => $this->recommended_value,
        ];
    }

    /**
     * Populate the Issue from an array.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data): void {
        $this->id = $data['id'] ?? '';
        $this->severity = $data['severity'] ?? '';
        $this->message = $data['message'] ?? '';
        $this->field = $data['field'] ?? '';
        $this->current_value = $data['current_value'] ?? null;
        $this->recommended_value = $data['recommended_value'] ?? null;
    }
}
