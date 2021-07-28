<?php

namespace App\Model;

use DigitalOceanV2\Entity\Droplet as DropletEntity;
use DigitalOceanV2\Entity\FloatingIp as FloatingIpEntity;

class AssignedIp
{
    private Instance $instance;
    private bool $hasInstance = false;

    public function __construct(
        private FloatingIpEntity $floatingIpEntity,
    ) {
        $dropletEntity = $floatingIpEntity->droplet;
        if ($dropletEntity instanceof DropletEntity) {
            $this->hasInstance = true;
            $this->instance = new Instance($dropletEntity);
        }
    }

    public function hasInstance(): bool
    {
        return $this->hasInstance;
    }

    public function getInstance(): Instance
    {
        return $this->instance;
    }

    public function getIp(): string
    {
        return $this->floatingIpEntity->ip;
    }

    public function withInstance(Instance $instance): self
    {
        $new = clone $this;
        $new->hasInstance = true;
        $new->instance = $instance;

        return $new;
    }
}
