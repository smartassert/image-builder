<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Services\InstanceHydrator;
use App\Tests\Services\HttpResponseFactory;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceHydratorTest extends KernelTestCase
{
    private InstanceHydrator $instanceHydrator;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceHydrator = self::getContainer()->get(InstanceHydrator::class);
        \assert($instanceHydrator instanceof InstanceHydrator);
        $this->instanceHydrator = $instanceHydrator;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    public function testHydrate(): void
    {
        $version = 'version-string';
        $messageQueueSize = 8;

        $this->mockHandler->append($this->httpResponseFactory->createFromArray([
            HttpResponseFactory::KEY_STATUS_CODE => 200,
            HttpResponseFactory::KEY_BODY => json_encode([
                'version' => $version,
                'message-queue-size' => $messageQueueSize,
            ]),
        ]));

        $dropletData = [
            'id' => 123,
        ];

        $dropletEntity = new DropletEntity($dropletData);
        $instance = new Instance($dropletEntity);

        self::assertNull($instance->getVersion());
        self::assertNull($instance->getMessageQueueSize());

        $instance = $this->instanceHydrator->hydrate($instance);
        self::assertSame($version, $instance->getVersion());
        self::assertSame($messageQueueSize, $instance->getMessageQueueSize());
    }
}
