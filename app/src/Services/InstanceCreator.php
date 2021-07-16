<?php

namespace App\Services;

use App\Model\Instance;
use DigitalOceanV2\Api\Droplet as DropletApi;
use DigitalOceanV2\Entity\Droplet;
use DigitalOceanV2\Exception\ExceptionInterface as VendorExceptionInterface;
use SmartAssert\DigitalOceanDropletConfiguration\Factory;

class InstanceCreator
{
    public function __construct(
        private DropletApi $dropletApi,
        private Factory $dropletConfigurationFactory,
        private string $instanceName,
    ) {
    }

    /**
     * @throws VendorExceptionInterface
     */
    public function create(): Instance
    {
        $configuration = $this->dropletConfigurationFactory->create();

        $dropletEntity = $this->dropletApi->create(
            $this->instanceName,
            $configuration->getRegion(),
            $configuration->getSize(),
            $configuration->getImage(),
            $configuration->getBackups(),
            $configuration->getIpv6(),
            $configuration->getVpcUuid(),
            $configuration->getSshKeys(),
            $configuration->getUserData(),
            $configuration->getMonitoring(),
            $configuration->getVolumes(),
            $configuration->getTags()
        );

        return new Instance($dropletEntity instanceof Droplet ? $dropletEntity : new Droplet());
    }
}
