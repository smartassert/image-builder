<?php

namespace App\Model;

class InstanceHealth implements \JsonSerializable
{
    /**
     * @var array<string, InstanceServiceAvailabilityInterface::AVAILABILITY_*>
     */
    private array $componentAvailabilities = [];

    /**
     * @param array<mixed> $componentAvailabilities
     */
    public function __construct(
        array $componentAvailabilities,
    ) {
        foreach ($componentAvailabilities as $key => $value) {
            if (
                is_string($key)
                && (
                    InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE === $value
                    || InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE === $value
                )
            ) {
                $this->componentAvailabilities[$key] = $value;
            }
        }
    }

    public function isAvailable(): bool
    {
        return (bool) array_reduce($this->componentAvailabilities, function (?bool $carry, string $item) {
            if (null === $carry) {
                $carry = true;
            }

            return $carry && InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE === $item;
        });
    }

    /**
     * @return array<string, InstanceServiceAvailabilityInterface::AVAILABILITY_*>
     */
    public function getComponentAvailabilities(): array
    {
        return $this->componentAvailabilities;
    }

    /**
     * @return array<string, InstanceServiceAvailabilityInterface::AVAILABILITY_*>
     */
    public function jsonSerialize(): array
    {
        return $this->componentAvailabilities;
    }
}
