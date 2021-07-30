<?php

namespace App\Tests\Unit\Model;

use App\Model\Instance;
use App\Model\InstanceCollection;
use App\Tests\Services\InstanceFactory;
use PHPUnit\Framework\TestCase;
use webignition\ObjectReflector\ObjectReflector;

class InstanceCollectionTest extends TestCase
{
    /**
     * @dataProvider getLatestDataProvider
     */
    public function testGetLatest(InstanceCollection $collection, ?Instance $expectedLatest): void
    {
        self::assertSame($expectedLatest, $collection->getLatest());
    }

    /**
     * @return array<mixed>
     */
    public function getLatestDataProvider(): array
    {
        $instanceWithoutVersion = InstanceFactory::create(['id' => 'no-version']);

        $instance01 = InstanceFactory::create(['id' => 'version-01'], '0.1');
        $instance02 = InstanceFactory::create(['id' => 'version-02'], '0.2');
        $instance03 = InstanceFactory::create(['id' => 'version-03'], '0.3');

        return [
            'empty' => [
                'collection' => new InstanceCollection([]),
                'expectedLatest' => null,
            ],
            'single instance, no version' => [
                'collection' => new InstanceCollection([
                    $instanceWithoutVersion,
                ]),
                'expectedLatest' => null,
            ],
            'single instance, has version' => [
                'collection' => new InstanceCollection([
                    $instance01,
                ]),
                'expectedLatest' => $instance01,
            ],
            'many instances, some with versions, some without' => [
                'collection' => new InstanceCollection([
                    $instance02,
                    $instance03,
                    $instanceWithoutVersion,
                    $instance01,
                ]),
                'expectedLatest' => $instance03,
            ],
        ];
    }

    /**
     * @dataProvider sortByCreatedDateDataProvider
     */
    public function testSortByCreatedDate(InstanceCollection $collection, InstanceCollection $expectedCollection): void
    {
        $originalInstances = ObjectReflector::getProperty($collection, 'instances');

        $sortedCollection = $collection->sortByCreatedDate();

        self::assertEquals($expectedCollection, $sortedCollection);
        self::assertSame($originalInstances, ObjectReflector::getProperty($collection, 'instances'));
    }

    /**
     * @return array<mixed>
     */
    public function sortByCreatedDateDataProvider(): array
    {
        $sortedCollection = $this->createSortedCollection();
        $reverseSortedCollection = $this->createReverseSortedCollection();

        return [
            'empty' => [
                'collection' => new InstanceCollection([]),
                'expectedCollection' => new InstanceCollection([]),
            ],
            'already sorted' => [
                'collection' => $sortedCollection,
                'expectedCollection' => $sortedCollection,
            ],
            'started reverse sorted' => [
                'collection' => $reverseSortedCollection,
                'expectedCollection' => $sortedCollection,
            ],
        ];
    }

    /**
     * @dataProvider getNewestDataProvider
     */
    public function testGetNewest(InstanceCollection $collection, ?Instance $expectedNewest): void
    {
        self::assertEquals($expectedNewest, $collection->getNewest());
    }

    /**
     * @return array<mixed>
     */
    public function getNewestDataProvider(): array
    {
        $sortedCollection = $this->createSortedCollection();
        $reverseSortedCollection = $this->createReverseSortedCollection();
        $expectedNewest = $sortedCollection->getFirst();

        return [
            'empty' => [
                'collection' => new InstanceCollection([]),
                'expectedNewest' => null,
            ],
            'sorted' => [
                'collection' => $sortedCollection,
                'expectedNewest' => $expectedNewest,
            ],
            'reverse sorted' => [
                'collection' => $reverseSortedCollection,
                'expectedNewest' => $expectedNewest,
            ],
        ];
    }

    private function createSortedCollection(): InstanceCollection
    {
        return new InstanceCollection([
            InstanceFactory::create([
                'id' => 123,
                'created_at' => '2021-07-30T16:36:31Z'
            ]),
            InstanceFactory::create([
                'id' => 465,
                'created_at' => '2021-07-29T16:36:31Z'
            ]),
            InstanceFactory::create([
                'id' => 789,
                'created_at' => '2021-07-28T16:36:31Z'
            ]),
        ]);
    }

    private function createReverseSortedCollection(): InstanceCollection
    {
        return new InstanceCollection([
            InstanceFactory::create([
                'id' => 789,
                'created_at' => '2021-07-28T16:36:31Z'
            ]),
            InstanceFactory::create([
                'id' => 465,
                'created_at' => '2021-07-29T16:36:31Z'
            ]),
            InstanceFactory::create([
                'id' => 123,
                'created_at' => '2021-07-30T16:36:31Z'
            ]),
        ]);
    }
}
