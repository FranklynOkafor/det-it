<?php

namespace DetIt\Infrastructure\Serialization;

use DetIt\Contracts\SerializableDTO;

/**
 * Class JsonSerializer
 *
 * Handles serialization and deserialization of DTOs to and from JSON format.
 */
class JsonSerializer implements Serializer {
    /**
     * Serialize a SerializableDTO to a JSON string.
     *
     * @param SerializableDTO $dto
     * @return string
     */
    public function serialize(SerializableDTO $dto): string {
        return json_encode($dto->toArray());
    }

    /**
     * Deserialize a JSON string back into a specific DTO class.
     *
     * @param string $data
     * @param string $class
     * @return SerializableDTO|null
     */
    public function deserialize(string $data, string $class): ?SerializableDTO {
        if (!class_exists($class)) {
            return null;
        }

        $decodedData = json_decode($data, true);
        if (!is_array($decodedData)) {
            return null;
        }

        /** @var SerializableDTO $instance */
        $instance = new $class();
        
        if ($instance instanceof SerializableDTO) {
            $instance->fromArray($decodedData);
            return $instance;
        }

        return null;
    }
}
