<?php

namespace App\Services;

use App\Model\Instance;
use App\Model\InstanceStatus;
use Psr\Http\Client\ClientExceptionInterface;

class InstanceHydrator
{
    public function __construct(
        private InstanceClient $instanceClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function hydrateVersion(Instance $instance): Instance
    {
        return $instance->withVersion(
            $this->instanceClient->getVersion($instance)
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function hydrate(Instance $instance): Instance
    {
        $status = $this->instanceClient->getStatus($instance);
        if ($status instanceof InstanceStatus) {
            $instance = $instance->withVersion($status->getVersion());
            $instance = $instance->withMessageQueueSize($status->getMessageQueueSize());
        }

        return $instance;
    }
}
