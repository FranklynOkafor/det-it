<?php

namespace DetIt\Domain\DTO;

use DetIt\Contracts\SerializableDTO;

/**
 * Class FixPlan
 *
 * Represents a plan of actions to fix SEO issues.
 */
class FixPlan implements SerializableDTO {
    /**
     * @var array List of actions to be taken.
     */
    private array $actions;

    /**
     * @var array Meta updates to apply.
     */
    private array $meta_updates;

    /**
     * @var array Image updates to apply.
     */
    private array $image_updates;

    /**
     * Constructor for FixPlan.
     *
     * @param array $actions
     * @param array $meta_updates
     * @param array $image_updates
     */
    public function __construct(
        array $actions = [],
        array $meta_updates = [],
        array $image_updates = []
    ) {
        $this->actions = $actions;
        $this->meta_updates = $meta_updates;
        $this->image_updates = $image_updates;
    }

    /**
     * Get the planned actions.
     *
     * @return array
     */
    public function getActions(): array {
        return $this->actions;
    }

    /**
     * Get the meta updates.
     *
     * @return array
     */
    public function getMetaUpdates(): array {
        return $this->meta_updates;
    }

    /**
     * Get the image updates.
     *
     * @return array
     */
    public function getImageUpdates(): array {
        return $this->image_updates;
    }

    /**
     * Convert the FixPlan to an array.
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'actions' => $this->actions,
            'meta_updates' => $this->meta_updates,
            'image_updates' => $this->image_updates,
        ];
    }

    /**
     * Populate the FixPlan from an array.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data): void {
        $this->actions = $data['actions'] ?? [];
        $this->meta_updates = $data['meta_updates'] ?? [];
        $this->image_updates = $data['image_updates'] ?? [];
    }
}
