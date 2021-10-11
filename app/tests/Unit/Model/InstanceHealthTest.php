<?php

namespace App\Tests\Unit\Model;

use App\Model\InstanceHealth;
use PHPUnit\Framework\TestCase;

class InstanceHealthTest extends TestCase
{
    public function testIsAvailable(): void
    {
        self::assertTrue(
            (new InstanceHealth(true, []))->isAvailable()
        );

        self::assertFalse(
            (new InstanceHealth(false, []))->isAvailable()
        );
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(
            [],
            (new InstanceHealth(true, []))->jsonSerialize()
        );

        $componentAvailabilities = [
            'service1' => 'available',
            'service2' => 'available',
            'service3' => 'unavailable',
        ];

        self::assertSame(
            $componentAvailabilities,
            (new InstanceHealth(true, $componentAvailabilities))->jsonSerialize()
        );
    }
}
