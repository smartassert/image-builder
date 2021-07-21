<?php

namespace App\Tests\Unit\Model;

use App\Model\Instance;
use App\Model\InstanceCollection;
use App\Tests\Services\InstanceFactory;
use PHPUnit\Framework\TestCase;

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
}
