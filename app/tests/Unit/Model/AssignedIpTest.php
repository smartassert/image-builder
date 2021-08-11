<?php

namespace App\Tests\Unit\Model;

use App\Model\AssignedIp;
use App\Tests\Services\InstanceFactory;
use DigitalOceanV2\Entity\FloatingIp as FloatingIpEntity;
use PHPUnit\Framework\TestCase;

class AssignedIpTest extends TestCase
{
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
}
