<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Services\InstanceRepository;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceRepositoryTest extends KernelTestCase
{
    private InstanceRepository $instanceRepository;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceRepository = self::getContainer()->get(InstanceRepository::class);
        \assert($instanceRepository instanceof InstanceRepository);
        $this->instanceRepository = $instanceRepository;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;
    }

    /**
     * @dataProvider findAllDataProvider
     *
     * @param Instance[] $expectedInstances
     */
    public function testFindAll(ResponseInterface $httpResponse, array $expectedInstances): void
    {
        $this->mockHandler->append($httpResponse);

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
                'httpResponse' => new Response(
                    200,
                    [
                        'content-type' => 'application/json; charset=utf-8',
                    ],
                    (string) json_encode([
                        'droplets' => [],
                    ])
                ),
                'expectedInstances' => [],
            ],
            'one' => [
                'httpResponse' => new Response(
                    200,
                    [
                        'content-type' => 'application/json; charset=utf-8',
                    ],
                    (string) json_encode([
                        'droplets' => [
                            [
                                'id' => 123,
                            ],
                        ],
                    ])
                ),
                'expectedInstances' => [
                    new Instance(new DropletEntity([
                        'id' => 123,
                    ])),
                ],
            ],
            'many' => [
                'httpResponse' => new Response(
                    200,
                    [
                        'content-type' => 'application/json; charset=utf-8',
                    ],
                    (string) json_encode([
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
                    ])
                ),
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
