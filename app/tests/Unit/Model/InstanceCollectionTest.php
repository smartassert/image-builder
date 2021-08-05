<?php

namespace App\Tests\Unit\Model;

use App\Model\Instance;
use App\Model\InstanceCollection;
use App\Tests\Services\InstanceFactory;
use PHPUnit\Framework\TestCase;

class InstanceCollectionTest extends TestCase
{
    /**
     * @dataProvider sortByCreatedDateDataProvider
     */
    public function testSortByCreatedDate(InstanceCollection $collection, InstanceCollection $expectedCollection): void
    {
        $originalCollection = clone $collection;
        $sortedCollection = $collection->sortByCreatedDate();

        self::assertEquals($expectedCollection, $sortedCollection);
        self::assertEquals($originalCollection, $collection);
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

    /**
     * @dataProvider filterByNotIpDataProvider
     */
    public function testFilterByNotIp(
        InstanceCollection $collection,
        string $ip,
        InstanceCollection $expectedCollection
    ): void {
        self::assertEquals(
            $expectedCollection,
            $collection->filterByNotIp($ip)
        );
    }

    /**
     * @return array<mixed>
     */
    public function filterByNotIpDataProvider(): array
    {
        $ip = '127.0.0.1';
        $instanceWithIp = InstanceFactory::create([
            'id' => 123,
            'networks' => [
                'v4' => [
                    [
                        'ip_address' => $ip,
                    ],
                ],
            ],
        ]);

        $instanceWithoutIp1 = InstanceFactory::create([
            'id' => 465,
            'networks' => [
                'v4' => [
                    [
                        'ip_address' => '127.0.0.2',
                    ],
                ],
            ],
        ]);

        $instanceWithoutIp2 = InstanceFactory::create([
            'id' => 789,
            'networks' => [
                'v4' => [
                    [
                        'ip_address' => '127.0.0.3',
                    ],
                ],
            ],
        ]);

        return [
            'empty' => [
                'collection' => new InstanceCollection([]),
                'ip' => 'not relevant',
                'expectedCollection' => new InstanceCollection([]),
            ],
            'single, has IP' => [
                'collection' => new InstanceCollection([
                    $instanceWithIp,
                ]),
                'ip' => $ip,
                'expectedCollection' => new InstanceCollection([]),
            ],
            'single, does not have IP' => [
                'collection' => new InstanceCollection([
                    $instanceWithoutIp1,
                ]),
                'ip' => $ip,
                'expectedCollection' => new InstanceCollection([
                    $instanceWithoutIp1,
                ]),
            ],
            'multiple, one has IP' => [
                'collection' => new InstanceCollection([
                    $instanceWithoutIp1,
                    $instanceWithIp,
                    $instanceWithoutIp2,
                ]),
                'ip' => $ip,
                'expectedCollection' => new InstanceCollection([
                    $instanceWithoutIp1,
                    $instanceWithoutIp2,
                ]),
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
