<?php

namespace App\Tests\Unit\Model;

use App\Model\InstanceHealth;
use App\Model\InstanceServiceAvailabilityInterface;
use PHPUnit\Framework\TestCase;

class InstanceHealthTest extends TestCase
{
    /**
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable(InstanceHealth $instanceHealth, bool $expectedIsAvailable): void
    {
        self::assertSame($expectedIsAvailable, $instanceHealth->isAvailable());
    }

    /**
     * @return array<mixed>
     */
    public function isAvailableDataProvider(): array
    {
        return [
            'empty' => [
                'instanceHealth' => new InstanceHealth([]),
                'expectedIsAvailable' => false,
            ],
            'no valid values passed to constructor' => [
                'instanceHealth' => new InstanceHealth([
                    true => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    false => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'database' => true,
                    'message_queue' => 12,
                ]),
                'expectedIsAvailable' => false,
            ],
            'three services, none available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                ]),
                'expectedIsAvailable' => false,
            ],
            'three services, one available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                ]),
                'expectedIsAvailable' => false,
            ],
            'three services, two available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                ]),
                'expectedIsAvailable' => false,
            ],
            'three services, all available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                ]),
                'expectedIsAvailable' => true,
            ],
        ];
    }

    /**
     * @dataProvider isEmptyDataProvider
     */
    public function testIsEmpty(InstanceHealth $instanceHealth, bool $expectedIsEmpty): void
    {
        self::assertSame($expectedIsEmpty, $instanceHealth->isEmpty());
    }

    /**
     * @return array<mixed>
     */
    public function isEmptyDataProvider(): array
    {
        return [
            'empty' => [
                'instanceHealth' => new InstanceHealth([]),
                'expectedIsEmpty' => true,
            ],
            'no valid values passed to constructor' => [
                'instanceHealth' => new InstanceHealth([
                    true => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    false => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'database' => true,
                    'message_queue' => 12,
                ]),
                'expectedIsEmpty' => true,
            ],
            'three services, none available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                ]),
                'expectedIsEmpty' => false,
            ],
            'three services, one available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                ]),
                'expectedIsEmpty' => false,
            ],
            'three services, two available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_UNAVAILABLE,
                ]),
                'expectedIsEmpty' => false,
            ],
            'three services, all available' => [
                'instanceHealth' => new InstanceHealth([
                    'service1' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service2' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                    'service3' => InstanceServiceAvailabilityInterface::AVAILABILITY_AVAILABLE,
                ]),
                'expectedIsEmpty' => false,
            ],
        ];
    }
}
