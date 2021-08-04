<?php

namespace App\Model\CommandOutput;

class CommandOutput implements \JsonSerializable
{
    /**
     * @param array<mixed> $context
     */
    public function __construct(
        private string $id,
        private array $context = [],
    ) {
    }

    /**
     * @return array{"id": string, "context"?: array<mixed>}
     */
    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
        ];

        if ([] !== $this->context) {
            $data['context'] = $this->context;
        }

        return $data;
    }
}
