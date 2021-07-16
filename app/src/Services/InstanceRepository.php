<?php

namespace App\Services;

use App\Model\Instance;
use App\Model\InstanceCollection;
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
     */
    public function findAll(): InstanceCollection
    {
        $dropletEntities = $this->dropletApi->getAll($this->dropletTag);

        $instances = [];
        foreach ($dropletEntities as $dropletEntity) {
            $instances[] = new Instance($dropletEntity);
        }

        return new InstanceCollection($instances);
    }
}
