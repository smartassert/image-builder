<?php

namespace App\Tests\Functional\Services;

use App\Model\FloatingIpAssignmentAction;
use App\Model\Instance;
use App\Services\FloatingIpManager;
use App\Tests\Services\HttpResponseFactory;
use App\Tests\Services\InstanceFactory;
use DigitalOceanV2\Entity\Action as ActionEntity;
use DigitalOceanV2\Entity\FloatingIp as FloatingIpEntity;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FloatingIpManagerTest extends KernelTestCase
{
    private FloatingIpManager $floatingIpManager;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $floatingIpManager = self::getContainer()->get(FloatingIpManager::class);
        \assert($floatingIpManager instanceof FloatingIpManager);
        $this->floatingIpManager = $floatingIpManager;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider assignDataProvider
     *
     * @param array<mixed> $httpResponseDataCollection
     */
    public function testAssign(
        array $httpResponseDataCollection,
        Instance $instance,
        FloatingIpAssignmentAction $expectedAction
    ): void {
        foreach ($httpResponseDataCollection as $httpResponseData) {
            $this->mockHandler->append(
                $this->httpResponseFactory->createFromArray($httpResponseData)
            );
        }

        $action = $this->floatingIpManager->assign($instance);

        self::assertEquals($expectedAction, $action);
    }

    /**
     * @return array<mixed>
     */
    public function assignDataProvider(): array
    {
        $instance123 = InstanceFactory::create(['id' => 123]);
        $instance456 = InstanceFactory::create(['id' => 456]);

        return [
            'no existing floating IP' => [
                'httpResponseDataCollection' => [
                    'repository response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'floating_ips' => [],
                        ]),
                    ],
                    'assign to droplet response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 202,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'floating_ip' => [
                                'ip' => '127.0.0.100',
                            ],
                        ]),
                    ],
                ],
                'instance' => $instance123,
                'expectedAction' => new FloatingIpAssignmentAction(
                    '127.0.0.100',
                    $instance123
                ),
            ],
            'has existing floating IP' => [
                'httpResponseDataCollection' => [
                    'repository response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'floating_ips' => [
                                [
                                    'ip' => '127.0.0.200',
                                    'droplet' => [
                                        'id' => 123,
                                        'tags' => [
                                            'worker-manager',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                    'assign to droplet response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 202,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'action' => [
                                'id' => 001,
                                'type' => 'assign_ip',
                            ],
                        ]),
                    ],
                ],
                'instance' => $instance456,
                'expectedAction' => (new FloatingIpAssignmentAction(
                    '127.0.0.200',
                    $instance456
                ))->withActionEntity(
                    new ActionEntity([
                        'id' => 001,
                        'type' => 'assign_ip',
                    ])
                ),
            ],
        ];
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param array<mixed> $httpResponseData
     */
    public function testCreate(
        array $httpResponseData,
        Instance $instance,
        FloatingIpEntity $expectedFloatingIp
    ): void {
        $this->mockHandler->append(
            $this->httpResponseFactory->createFromArray($httpResponseData)
        );

        $floatingIP = $this->floatingIpManager->create($instance);

        self::assertEquals($expectedFloatingIp, $floatingIP);
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no existing floating IP' => [
                'httpResponseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 200,
                    HttpResponseFactory::KEY_HEADERS => [
                        'content-type' => 'application/json; charset=utf-8',
                    ],
                    HttpResponseFactory::KEY_BODY => (string) json_encode([
                        'floating_ip' => [
                            'ip' => '127.0.0.100',
                        ],
                    ]),
                ],
                'instance' => InstanceFactory::create(['id' => 123]),
                'expectedFloatingIp' => new FloatingIpEntity([
                    'ip' => '127.0.0.100',
                ]),
            ],
        ];
    }
}
