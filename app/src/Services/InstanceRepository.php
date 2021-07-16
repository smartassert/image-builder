<?php

namespace App\Services;

use App\Model\Instance;
use DigitalOceanV2\Api\Droplet as DropletApi;
use DigitalOceanV2\Exception\ExceptionInterface;

class InstanceRepository
{
    public function __construct(
        private DropletApi $dropletApi,
        private string $dropletTag
    ) {
    }

    /**
     * @throws ExceptionInterface
     *
     * @return Instance[]
     */
    public function findAll(): array
    {
        $dropletEntities = $this->dropletApi->getAll($this->dropletTag);

        $instances = [];
        foreach ($dropletEntities as $dropletEntity) {
            $instances[] = new Instance($dropletEntity);
        }

        return $instances;
    }
}
