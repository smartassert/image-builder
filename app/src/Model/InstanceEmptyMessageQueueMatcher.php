<?php

namespace App\Model;

class InstanceEmptyMessageQueueMatcher implements InstanceMatcherInterface
{
    public function matches(Instance $instance): bool
    {
        return 0 === $instance->getMessageQueueSize();
    }
}
