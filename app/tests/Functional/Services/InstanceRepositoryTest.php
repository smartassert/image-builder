<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Services\InstanceRepository;
use App\Tests\Services\HttpResponseFactory;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceRepositoryTest extends KernelTestCase
{
    private InstanceRepository $instanceRepository;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceRepository = self::getContainer()->get(InstanceRepository::class);
        \assert($instanceRepository instanceof InstanceRepository);
        $this->instanceRepository = $instanceRepository;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider findAllDataProvider
     *
     * @param Instance[] $expectedInstances
     */
    public function testFindAll(string $httpResponseBody, array $expectedInstances): void
    {
        $this->mockHandler->append(
            $this->httpResponseFactory->createFromArray([
                HttpResponseFactory::KEY_STATUS_CODE => 200,
                HttpResponseFactory::KEY_HEADERS => [
                    'content-type' => 'application/json; charset=utf-8',
                ],
                HttpResponseFactory::KEY_BODY => $httpResponseBody,
            ])
        );

        $instances = $this->instanceRepository->findAll();

        self::assertCount(count($expectedInstances), $instances);

        foreach ($instances as $instanceIndex => $instance) {
            $expectedInstance = $expectedInstances[$instanceIndex];
            self::assertSame($expectedInstance->getId(), $instance->getId());
        }
    }

    /**
     * @return array<mixed>
     */
    public function findAllDataProvider(): array
    {
        return [
            'none' => [
                'httpResponseBody' => (string) json_encode([
                    'droplets' => [],
                ]),
                'expectedInstances' => [],
            ],
            'one' => [
                'httpResponseBody' => (string) json_encode([
                    'droplets' => [
                        [
                            'id' => 123,
                        ],
                    ],
                ]),
                'expectedInstances' => [
                    new Instance(new DropletEntity([
                        'id' => 123,
                    ])),
                ],
            ],
            'many' => [
                'httpResponseBody' => (string) json_encode([
                    'droplets' => [
                        [
                            'id' => 123,
                        ],
                        [
                            'id' => 456,
                        ],
                        [
                            'id' => 789,
                        ],
                    ],
                ]),
                'expectedInstances' => [
                    new Instance(new DropletEntity([
                        'id' => 123,
                    ])),
                    new Instance(new DropletEntity([
                        'id' => 456,
                    ])),
                    new Instance(new DropletEntity([
                        'id' => 789,
                    ])),
                ],
            ],
        ];
    }
}
