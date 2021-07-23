<?php

namespace App\Services;

use App\Model\Instance;
use App\Model\InstanceCollection;
use DigitalOceanV2\Api\Droplet as DropletApi;
use DigitalOceanV2\Exception\ExceptionInterface;
use DigitalOceanV2\Exception\RuntimeException;

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

    /**
     * @throws ExceptionInterface
     */
    public function find(int $id): ?Instance
    {
        try {
            return new Instance($this->dropletApi->getById($id));
        } catch (ExceptionInterface $exception) {
            if ($exception instanceof RuntimeException && 404 === $exception->getCode()) {
                return null;
            }

            throw $exception;
        }
    }
}
