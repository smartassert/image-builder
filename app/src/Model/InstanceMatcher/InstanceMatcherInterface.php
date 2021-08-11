<?php

namespace App\Model\InstanceMatcher;

use App\Model\Instance;

interface InstanceMatcherInterface
{
    public function matches(Instance $instance): bool;
}
