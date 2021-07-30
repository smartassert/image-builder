<?php

namespace App\Model\CommandOutput;

class CommandOutput implements \JsonSerializable
{
    /**
     * @param array<mixed> $context
     */
    public function __construct(
        private string $id,
        private string $message,
        private array $context = [],
    ) {
    }

    /**
     * @return array{id: string, message: string}
     */
    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'message' => $this->message,
        ];

        if ([] !== $this->context) {
            $data['context'] = $this->context;
        }

        return $data;
    }
}
