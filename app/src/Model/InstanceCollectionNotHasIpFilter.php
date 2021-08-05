<?php

namespace App\Model;

class InstanceCollectionNotHasIpFilter implements InstanceCollectionFilterInterface
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
