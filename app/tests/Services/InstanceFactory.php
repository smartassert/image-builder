<?php

namespace App\Tests\Services;

use App\Model\Instance;
use DigitalOceanV2\Entity\Droplet;

class InstanceFactory
{
    /**
     * @param array<mixed> $dropletData
     */
    public static function create(array $dropletData, ?string $version = null): Instance
    {
        $instance = new Instance(
            new Droplet($dropletData)
        );

        if (is_string($version)) {
            $instance = $instance->withVersion($version);
        }

        return $instance;
    }
}
