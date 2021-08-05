<?php

namespace App\Model;

class InstanceCollectionHasEmptyMessageQueueFilter implements InstanceCollectionFilterInterface
{
    public function matches(Instance $instance): bool
    {
        return 0 === $instance->getMessageQueueSize();
    }
}
