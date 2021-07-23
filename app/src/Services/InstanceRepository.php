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
        private string $instanceCollectionTag,
        private string $instanceTag,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function findAll(): InstanceCollection
    {
        $dropletEntities = $this->dropletApi->getAll($this->instanceCollectionTag);

        $instances = [];
        foreach ($dropletEntities as $dropletEntity) {
            $instances[] = new Instance($dropletEntity);
        }

        return new InstanceCollection($instances);
    }

    /**
     * @throws ExceptionInterface
     */
    public function findCurrent(): ?Instance
    {
        $droplets = $this->dropletApi->getAll($this->instanceTag);

        return 0 === count($droplets)
            ? null
            : new Instance($droplets[0]);
    }
}
