<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Services\InstanceCreator;
use App\Tests\Services\HttpResponseFactory;
use App\Tests\Services\InstanceFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceCreatorTest extends KernelTestCase
{
    private InstanceCreator $instanceCreator;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceCreator = self::getContainer()->get(InstanceCreator::class);
        \assert($instanceCreator instanceof InstanceCreator);
        $this->instanceCreator = $instanceCreator;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    public function testCreate(): void
    {
        $dropletData = [
            'id' => 123,
        ];

        $successResponseData = [
            HttpResponseFactory::KEY_STATUS_CODE => 202,
            HttpResponseFactory::KEY_HEADERS => [
                'content-type' => 'application/json; charset=utf-8',
            ],
            HttpResponseFactory::KEY_BODY => (string) json_encode([
                'droplet' => $dropletData,
            ]),
        ];

        $this->mockHandler->append(
            $this->httpResponseFactory->createFromArray($successResponseData)
        );
        $instance = $this->instanceCreator->create();

        self::assertInstanceOf(Instance::class, $instance);
        self::assertEquals(
            $instance = InstanceFactory::create($dropletData),
            $instance
        );
    }
}
