<?php

namespace App\Services;

use App\Model\FloatingIpAssignmentAction;
use App\Model\Instance;
use DigitalOceanV2\Api\FloatingIp as FloatingIpApi;
use DigitalOceanV2\Entity\FloatingIp as FloatingIpEntity;
use DigitalOceanV2\Exception\ExceptionInterface;

class FloatingIpManager
{
    public function __construct(
        private FloatingIpRepository $floatingIpRepository,
        private FloatingIpApi $floatingIpApi
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function assign(Instance $instance): FloatingIpAssignmentAction
    {
        $existingFloatingIp = $this->floatingIpRepository->find();
        if (null === $existingFloatingIp) {
            $floatingIpEntity = $this->create($instance);

            return new FloatingIpAssignmentAction(
                $floatingIpEntity->ip,
                $instance
            );
        }

        $actionEntity = $this->floatingIpApi->assign($existingFloatingIp, $instance->getId());

        return (
            new FloatingIpAssignmentAction(
                $existingFloatingIp,
                $instance
            )
        )->withActionEntity($actionEntity);
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(Instance $instance): FloatingIpEntity
    {
        return $this->floatingIpApi->createAssigned($instance->getId());
    }
}
