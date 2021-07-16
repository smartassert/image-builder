<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Services\InstanceCreator;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceCreatorTest extends KernelTestCase
{
    private InstanceCreator $instanceCreator;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceCreator = self::getContainer()->get(InstanceCreator::class);
        \assert($instanceCreator instanceof InstanceCreator);
        $this->instanceCreator = $instanceCreator;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;
    }

    public function testCreate(): void
    {
        $dropletData = [
            'id' => 123,
        ];

        $successResponse = new Response(
            202,
            [
                'content-type' => 'application/json; charset=utf-8',
            ],
            (string) json_encode([
                'droplet' => $dropletData,
            ])
        );

        $this->mockHandler->append($successResponse);
        $instance = $this->instanceCreator->create();

        self::assertInstanceOf(Instance::class, $instance);
        self::assertEquals(
            new Instance(
                new DropletEntity($dropletData)
            ),
            $instance
        );
    }
}
