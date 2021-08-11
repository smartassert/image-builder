<?php

namespace App\Model;

use DigitalOceanV2\Entity\Snapshot;

class Image
{
    public function __construct(private Snapshot $snapshot)
    {
    }

    public function getId(): string
    {
        return $this->snapshot->id;
    }

    public function getSnapshot(): Snapshot
    {
        return $this->snapshot;
    }
}
