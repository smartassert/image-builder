<?php

namespace App\Model;

class InstanceStatus
{
    public function __construct(
        private string $version,
        private int $messageQueueSize,
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getMessageQueueSize(): int
    {
        return $this->messageQueueSize;
    }
}
