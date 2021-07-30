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

    public function getFirst(): ?Instance
    {
        return 0 === count($this)
            ? null
            : $this->instances[0];
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

    public function getNewest(): ?Instance
    {
        $sortedCollection = $this->sortByCreatedDate();

        return $sortedCollection->getFirst();
    }

    public function sortByCreatedDate(): InstanceCollection
    {
        $instances = $this->instances;

        usort($instances, function (Instance $a, Instance $b): int {
            $aTimestamp = $a->getCreatedAt()->getTimestamp();
            $bTimestamp = $b->getCreatedAt()->getTimestamp();

            if ($aTimestamp === $bTimestamp) {
                return 0;
            }

            return $aTimestamp < $bTimestamp ? 1 : -1;
        });

        return new InstanceCollection($instances);
    }
}
