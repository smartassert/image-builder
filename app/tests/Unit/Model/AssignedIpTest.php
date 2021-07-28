<?php

namespace App\Tests\Unit\Model;

use App\Model\AssignedIp;
use App\Tests\Services\InstanceFactory;
use DigitalOceanV2\Entity\FloatingIp as FloatingIpEntity;
use PHPUnit\Framework\TestCase;

class AssignedIpTest extends TestCase
{
    /**
     * @dataProvider hasInstanceDataProvider
     */
    public function testHasInstance(AssignedIp $assignedIp, bool $expectedHasInstance): void
    {
        self::assertSame($expectedHasInstance, $assignedIp->hasInstance());
    }

    /**
     * @return array<mixed>
     */
    public function hasInstanceDataProvider(): array
    {
        return [
            'not has instance' => [
                'assignedIp' => new AssignedIp(new FloatingIpEntity([
                    'ip' => '127.0.0.1',
                ])),
                'expectedHasInstance' => false,
            ],
            'has instance' => [
                'assignedIp' => new AssignedIp(new FloatingIpEntity([
                    'ip' => '127.0.0.1',
                    'droplet' => (object) [
                        'id' => 123,
                    ],
                ])),
                'expectedHasInstance' => true,
            ],
        ];
    }

    public function testGetInstance(): void
    {
        $assignedIp = new AssignedIp(new FloatingIpEntity([
            'ip' => '127.0.0.1',
            'droplet' => (object) [
                'id' => 123,
            ],
        ]));

        $expectedInstance = InstanceFactory::create([
            'id' => 123,
        ]);

        self::assertEquals($expectedInstance, $assignedIp->getInstance());
    }

    public function testGetIp(): void
    {
        $assignedIp = new AssignedIp(new FloatingIpEntity([
            'ip' => '127.0.0.1',
            'droplet' => (object) [
                'id' => 123,
            ],
        ]));

        self::assertEquals('127.0.0.1', $assignedIp->getIp());
    }

    public function testWithInstance(): void
    {
        $assignedIp = new AssignedIp(new FloatingIpEntity([
            'ip' => '127.0.0.1',
            'droplet' => null,
        ]));

        self::assertFalse($assignedIp->hasInstance());

        $expectedInstance = InstanceFactory::create([
            'id' => 123,
        ]);

        $assignedIp = $assignedIp->withInstance($expectedInstance);
        self::assertTrue($assignedIp->hasInstance());
        self::assertSame($expectedInstance, $assignedIp->getInstance());
    }
}
