<?php

namespace DetIt\Contracts;

/**
 * Interface SerializableDTO
 *
 * Defines a standard contract for DTOs that can be serialized to and from arrays.
 */
interface SerializableDTO {
    /**
     * Convert the DTO to an associative array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Populate the DTO from an associative array.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data): void;
}
