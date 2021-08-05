<?php

namespace App\Model;

interface InstanceMatcherInterface
{
    public function matches(Instance $instance): bool;
}
