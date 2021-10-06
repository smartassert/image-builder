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
    public function hydrate(Instance $instance): Instance
    {
        $status = $this->instanceClient->getStatus($instance);
        if ($status instanceof InstanceStatus) {
            $instance = $instance->withMessageQueueSize($status->getMessageQueueSize());
        }

        return $instance->withAdditionalState(
            $this->instanceClient->getState($instance)
        );
    }
}
