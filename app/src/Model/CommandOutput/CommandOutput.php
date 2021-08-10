<?php

namespace App\Model\CommandOutput;

class CommandOutput implements \JsonSerializable
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    /**
     * @param self::STATUS_* $status
     * @param array<mixed>   $context
     */
    public function __construct(
        private string $status,
        private string $id,
        private array $context = [],
    ) {
    }

    /**
     * @param array<mixed> $context
     */
    public static function createSuccess(string $id, array $context = []): self
    {
        return new CommandOutput(self::STATUS_SUCCESS, $id, $context);
    }

    /**
     * @param array<mixed> $context
     */
    public static function createError(string $id, array $context = []): self
    {
        return new CommandOutput(self::STATUS_ERROR, $id, $context);
    }

    /**
     * @return array{"status": string, "id": string, "context"?: array<mixed>}
     */
    public function jsonSerialize(): array
    {
        $data = [
            'status' => $this->status,
            'id' => $this->id,
        ];

        if ([] !== $this->context) {
            $data['context'] = $this->context;
        }

        return $data;
    }
}
