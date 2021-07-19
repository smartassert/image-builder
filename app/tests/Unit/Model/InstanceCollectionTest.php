<?php

namespace App\Tests\Unit\Model;

use App\Model\Instance;
use App\Model\InstanceCollection;
use DigitalOceanV2\Entity\Droplet;
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
        $instanceWithoutVersion = new Instance(
            new Droplet([
                'id' => 'no-version',
            ])
        );

        $instance01 = (new Instance(
            new Droplet([
                'id' => 'version-01',
            ])
        ))->withVersion('0.1');

        $instance02 = (new Instance(
            new Droplet([
                'id' => 'version-02',
            ])
        ))->withVersion('0.2');

        $instance03 = (new Instance(
            new Droplet([
                'id' => 'version-03',
            ])
        ))->withVersion('0.3');

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
