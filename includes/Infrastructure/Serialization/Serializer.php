<?php

namespace DetIt\Infrastructure\Serialization;

use DetIt\Contracts\SerializableDTO;

/**
 * Interface Serializer
 *
 * Defines the contract for serializing and deserializing DTOs.
 */
interface Serializer {
    /**
     * Serialize a SerializableDTO to a string representation.
     *
     * @param SerializableDTO $dto
     * @return string
     */
    public function serialize(SerializableDTO $dto): string;

    /**
     * Deserialize a string representation back into a specific DTO class.
     *
     * @param string $data
     * @param string $class The fully qualified class name of the target DTO.
     * @return SerializableDTO|null
     */
    public function deserialize(string $data, string $class): ?SerializableDTO;
}
