<?php

namespace App\Model\InstanceMatcher;

use App\Model\Instance;

class InstanceEmptyMessageQueueMatcher implements InstanceMatcherInterface
{
    public function matches(Instance $instance): bool
    {
        return 0 === $instance->getMessageQueueSize();
    }
}
