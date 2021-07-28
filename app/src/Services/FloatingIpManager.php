<?php

namespace App\Services;

use App\Model\Instance;
use DigitalOceanV2\Api\FloatingIp as FloatingIpApi;
use DigitalOceanV2\Entity\Action as ActionEntity;
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
    public function create(Instance $instance): FloatingIpEntity
    {
        return $this->floatingIpApi->createAssigned($instance->getId());
    }

    /**
     * @throws ExceptionInterface
     */
    public function reAssign(Instance $instance, string $ip): ActionEntity
    {
        return $this->floatingIpApi->assign($ip, $instance->getId());
    }
}
