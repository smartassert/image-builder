<?php

namespace App\Model;

class InstanceNotHasIpMatcher implements InstanceMatcherInterface
{
    public function __construct(
        private string $ip
    ) {
    }

    public function matches(Instance $instance): bool
    {
        return false === $instance->hasIp($this->ip);
    }
}
