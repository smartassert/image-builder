<?php

namespace App\Tests\Functional\Services;

use App\Model\InstanceStatus;
use App\Services\InstanceClient;
use App\Tests\Services\HttpResponseFactory;
use App\Tests\Services\InstanceFactory;
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

        $instance = InstanceFactory::create([
            'id' => 123,
        ]);

        self::assertSame($version, $this->instanceClient->getVersion($instance));
    }

    /**
     * @dataProvider getStatusReturnsNullDataProvider
     */
    public function testGetStatusReturnsNull(string $responseBody): void
    {
        $this->mockHandler->append($this->httpResponseFactory->createFromArray([
            HttpResponseFactory::KEY_STATUS_CODE => 200,
            HttpResponseFactory::KEY_BODY => $responseBody,
        ]));

        $instance = InstanceFactory::create(['id' => 123]);

        self::assertNull($this->instanceClient->getStatus($instance));
    }

    /**
     * @return array<mixed>
     */
    public function getStatusReturnsNullDataProvider(): array
    {
        return [
            'response not an array' => [
                'responseBody' => 'string',
            ],
            'version not present' => [
                'responseBody' => json_encode([
                    'message-queue-size' => 2,
                ]),
            ],
            'version not a string' => [
                'responseBody' => json_encode([
                    'version' => true,
                    'message-queue-size' => 2,
                ]),
            ],
            'message-queue-size not present' => [
                'responseBody' => json_encode([
                    'version' => '0.8',
                ]),
            ],
            'message-queue-size not an integer' => [
                'responseBody' => json_encode([
                    'version' => '0.8',
                    'message-queue-size' => true,
                ]),
            ],
        ];
    }

    /**
     * @dataProvider getStatusReturnInstanceStatusDataProvider
     */
    public function testGetStatusReturnsInstanceStatus(
        string $responseBody,
        InstanceStatus $expectedInstanceStatus
    ): void {
        $this->mockHandler->append($this->httpResponseFactory->createFromArray([
            HttpResponseFactory::KEY_STATUS_CODE => 200,
            HttpResponseFactory::KEY_BODY => $responseBody,
        ]));

        $instance = InstanceFactory::create(['id' => 123]);

        self::assertEquals($expectedInstanceStatus, $this->instanceClient->getStatus($instance));
    }

    /**
     * @return array<mixed>
     */
    public function getStatusReturnInstanceStatusDataProvider(): array
    {
        return [
            'default' => [
                'responseBody' => json_encode([
                    'version' => '0.8',
                    'message-queue-size' => 12,
                ]),
                'expectedInstanceStatus' => new InstanceStatus('0.8', 12),
            ],
        ];
    }
}
