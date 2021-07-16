<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Services\InstanceClient;
use App\Tests\Services\HttpResponseFactory;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceClientTest extends KernelTestCase
{
    private InstanceClient $instanceClient;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceClient = self::getContainer()->get(InstanceClient::class);
        \assert($instanceClient instanceof InstanceClient);
        $this->instanceClient = $instanceClient;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    public function testGetVersion(): void
    {
        $version = 'version-string';

        $this->mockHandler->append($this->httpResponseFactory->createFromArray([
            HttpResponseFactory::KEY_STATUS_CODE => 200,
            HttpResponseFactory::KEY_BODY => $version,
        ]));

        $dropletData = [
            'id' => 123,
            'networks' => [
                'v4' => [
                    [
                        'type' => 'public',
                        'ip_address' => '127.0.0.1',
                    ],
                ],
            ],
        ];

        $dropletEntity = new DropletEntity($dropletData);
        $instance = new Instance($dropletEntity);

        self::assertSame($version, $this->instanceClient->getVersion($instance));
    }
}
