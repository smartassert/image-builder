<?php

namespace App\Model;

interface InstanceCollectionFilterInterface
{
    public function matches(Instance $instance): bool;
}
