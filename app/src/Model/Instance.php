<?php

namespace App\Model;

use DigitalOceanV2\Entity\Droplet;

class Instance
{
    public function __construct(private Droplet $droplet)
    {
    }

    public function getId(): int
    {
        return $this->droplet->id;
    }

    public function getDroplet(): Droplet
    {
        return $this->droplet;
    }
}
