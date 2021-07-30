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
