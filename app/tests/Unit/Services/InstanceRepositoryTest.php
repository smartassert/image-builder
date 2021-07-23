<?php

namespace App\Tests\Unit\Services;

use App\Services\InstanceRepository;
use DigitalOceanV2\Api\Droplet as DropletApi;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class InstanceRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCreate(): void
    {
        $droplets = [
            new DropletEntity([
                'id' => 123,
            ]),
            new DropletEntity([
                'id' => 456,
            ]),
        ];

        $dropletApi = \Mockery::mock(DropletApi::class);
        $dropletApi
            ->shouldReceive('getAll')
            ->with('worker-manager')
            ->andReturn($droplets)
        ;

        $instanceRepository = new InstanceRepository(
            $dropletApi,
            'worker-manager',
            'worker-manager-123456'
        );

        $instances = $instanceRepository->findAll();

        self::assertCount(count($droplets), $instances);

        foreach ($instances as $instanceId => $instance) {
            self::assertSame($droplets[$instanceId]->id, $instance->getId());
        }
    }
}
