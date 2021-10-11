<?php

namespace App\Model;

class InstanceHealth implements \JsonSerializable
{
    /**
     * @param array<mixed> $componentAvailabilities
     */
    public function __construct(
        private bool $isAvailable,
        private array $componentAvailabilities,
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @return array<mixed>
     */
    public function getComponentAvailabilities(): array
    {
        return $this->componentAvailabilities;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->componentAvailabilities;
    }
}
