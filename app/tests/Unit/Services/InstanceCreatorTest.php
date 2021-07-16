<?php

namespace App\Tests\Unit\Services;

use App\Model\Instance;
use App\Services\InstanceCreator;
use DigitalOceanV2\Api\Droplet as DropletApi;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use SmartAssert\DigitalOceanDropletConfiguration\Factory;

class InstanceCreatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCreate(): void
    {
        $dropletConfigurationFactory = new Factory();
        $dropletEntity = new DropletEntity();

        $dropletApi = \Mockery::mock(DropletApi::class);
        $dropletApi
            ->shouldReceive('create')
            ->with(
                'instance-prefix-0.4.5.6',
                '',
                '',
                '',
                false,
                false,
                false,
                [],
                '',
                true,
                [],
                [],
            )
            ->andReturn($dropletEntity)
        ;

        $instanceNameCreator = new InstanceCreator(
            $dropletApi,
            $dropletConfigurationFactory,
            'instance-prefix-0.4.5.6'
        );

        $instance = $instanceNameCreator->create();

        self::assertInstanceOf(Instance::class, $instance);
        self::assertSame($dropletEntity, $instance->getDroplet());
    }
}
