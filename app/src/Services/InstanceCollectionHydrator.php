<?php

namespace App\Services;

use App\Model\InstanceCollection;
use Psr\Http\Client\ClientExceptionInterface;

class InstanceCollectionHydrator
{
    public function __construct(
        private InstanceHydrator $instanceHydrator,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function hydrateVersions(InstanceCollection $instanceCollection): InstanceCollection
    {
        $hydratedInstances = [];

        foreach ($instanceCollection as $instance) {
            $hydratedInstances[] = $this->instanceHydrator->hydrateVersion($instance);
        }

        return new InstanceCollection($hydratedInstances);
    }
}
