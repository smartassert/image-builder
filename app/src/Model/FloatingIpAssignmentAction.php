<?php

namespace App\Model;

use DigitalOceanV2\Entity\Action as ActionEntity;

class FloatingIpAssignmentAction
{
    private ?ActionEntity $actionEntity;

    public function __construct(
        private string $ipAddress,
        private Instance $instance,
    ) {
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getInstance(): Instance
    {
        return $this->instance;
    }

    public function withActionEntity(ActionEntity $actionEntity): self
    {
        $new = clone $this;
        $new->actionEntity = $actionEntity;

        return $new;
    }

    /**
     * @return null|ActionStatusInterface::STATUS_*
     */
    public function getStatus(): ?string
    {
        if ($this->actionEntity instanceof ActionEntity) {
            $status = $this->actionEntity->status;

            if (ActionStatusInterface::STATUS_IN_PROGRESS === $status) {
                return ActionStatusInterface::STATUS_IN_PROGRESS;
            }

            if (ActionStatusInterface::STATUS_COMPLETED === $status) {
                return ActionStatusInterface::STATUS_COMPLETED;
            }

            if (ActionStatusInterface::STATUS_ERRORED === $status) {
                return ActionStatusInterface::STATUS_ERRORED;
            }

            return ActionStatusInterface::STATUS_UNKNOWN;
        }

        return null;
    }
}
