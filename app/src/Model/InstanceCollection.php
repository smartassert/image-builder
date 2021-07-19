<?php

namespace App\Model;

/**
 * @implements \IteratorAggregate<Instance>
 */
class InstanceCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var Instance[]
     */
    private array $instances;

    /**
     * @param Instance[] $instances
     */
    public function __construct(array $instances)
    {
        $this->instances = array_filter($instances, function ($item) {
            return $item instanceof Instance;
        });
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->instances);
    }

    public function count(): int
    {
        return count($this->instances);
    }

    public function getLatest(): ?Instance
    {
        $latest = null;
        $latestVersion = null;

        foreach ($this->instances as $instance) {
            $instanceVersion = $instance->getVersion();

            if (null !== $instanceVersion && $instanceVersion > $latestVersion) {
                $latestVersion = $instanceVersion;
                $latest = $instance;
            }
        }

        return $latest;
    }
}
