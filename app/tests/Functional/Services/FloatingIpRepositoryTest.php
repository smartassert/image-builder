<?php

namespace App\Tests\Functional\Services;

use App\Services\FloatingIpRepository;
use App\Tests\Services\HttpResponseFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FloatingIpRepositoryTest extends KernelTestCase
{
    private FloatingIpRepository $floatingIpRepository;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $floatingIpRepository = self::getContainer()->get(FloatingIpRepository::class);
        \assert($floatingIpRepository instanceof FloatingIpRepository);
        $this->floatingIpRepository = $floatingIpRepository;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider findDataProvider
     *
     * @param array<mixed> $floatingIpResponseData
     */
    public function testFind(array $floatingIpResponseData, ?string $expectedIp): void
    {
        $this->mockHandler->append(
            $this->httpResponseFactory->createFromArray([
                HttpResponseFactory::KEY_STATUS_CODE => 200,
                HttpResponseFactory::KEY_HEADERS => [
                    'content-type' => 'application/json; charset=utf-8',
                ],
                HttpResponseFactory::KEY_BODY => (string) json_encode([
                    'floating_ips' => $floatingIpResponseData,
                ]),
            ])
        );

        self::assertSame($expectedIp, $this->floatingIpRepository->find());
    }

    /**
     * @return array<mixed>
     */
    public function findDataProvider(): array
    {
        return [
            'none' => [
                'floatingIpResponseData' => [],
                'expectedIp' => null,
            ],
            'one, not assigned to anything' => [
                'floatingIpResponseData' => [
                    [
                        'ip' => '127.0.0.100',
                        'droplet' => null,
                    ],
                ],
                'expectedIp' => null,
            ],
            'one, assigned to an instance' => [
                'floatingIpResponseData' => [
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
                'expectedIp' => '127.0.0.200',
            ],
            'two, first assigned to an instance' => [
                'floatingIpResponseData' => [
                    [
                        'ip' => '127.0.0.300',
                        'droplet' => [
                            'id' => 123,
                            'tags' => [
                                'worker-manager',
                            ],
                        ],
                    ],
                    [
                        'ip' => '127.0.0.301',
                        'droplet' => [
                            'id' => 465,
                            'tags' => [],
                        ],
                    ],
                ],
                'expectedIp' => '127.0.0.300',
            ],
            'two, second assigned to an instance' => [
                'floatingIpResponseData' => [
                    [
                        'ip' => '127.0.0.400',
                        'droplet' => [
                            'id' => 123,
                            'tags' => [],
                        ],
                    ],
                    [
                        'ip' => '127.0.0.401',
                        'droplet' => [
                            'id' => 465,
                            'tags' => [
                                'worker-manager',
                            ],
                        ],
                    ],
                ],
                'expectedIp' => '127.0.0.401',
            ],
        ];
    }
}
