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

    public function hydrate(InstanceCollection $collection): InstanceCollection
    {
        $hydratedInstances = [];

        foreach ($collection as $instance) {
            try {
                $hydratedInstances[] = $this->instanceHydrator->hydrate($instance);
            } catch (ClientExceptionInterface) {
                // Intentionally ignore HTTP exceptions
            }
        }

        return new InstanceCollection($hydratedInstances);
    }
}
