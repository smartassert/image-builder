<?php

namespace App\Tests\Functional\Command;

use App\Command\IpCreateCommand;
use App\Tests\Services\HttpResponseFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class IpCreateCommandTest extends KernelTestCase
{
    private IpCreateCommand $command;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;
    private string $instanceTag;

    protected function setUp(): void
    {
        parent::setUp();

        $command = self::getContainer()->get(IpCreateCommand::class);
        \assert($command instanceof IpCreateCommand);
        $this->command = $command;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;

        $instanceTag = self::getContainer()->getParameter('instance_tag');
        \assert(is_string($instanceTag));
        $this->instanceTag = $instanceTag;
    }

    /**
     * @dataProvider runSuccessDataProvider
     *
     * @param array<mixed> $httpResponseDataCollection
     */
    public function testRunSuccess(
        array $httpResponseDataCollection,
        int $expectedExitCode,
        string $expectedOutput,
    ): void {
        foreach ($httpResponseDataCollection as $httpResponseData) {
            $this->mockHandler->append(
                $this->httpResponseFactory->createFromArray($httpResponseData)
            );
        }

        $output = new BufferedOutput();

        $exitCode = $this->command->run(new ArrayInput([]), $output);

        self::assertSame($expectedExitCode, $exitCode);

        $expectedOutput = str_replace(
            '{{ instance-tag }}',
            $this->instanceTag,
            $expectedOutput
        );

        self::assertJsonStringEqualsJsonString($expectedOutput, $output->fetch());
    }

    /**
     * @return array<mixed>
     */
    public function runSuccessDataProvider(): array
    {
        return [
            'no current instance' => [
                'httpResponseDataCollection' => [
                    'droplets response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [],
                        ])
                    ],
                ],
                'expectedExitCode' => IpCreateCommand::EXIT_CODE_NO_CURRENT_INSTANCE,
                'expectedOutput' => (string) json_encode([
                    'error' => [
                        'id' => 'no-instance',
                        'message' => 'Cannot assign IP, no current instance found',
                    ],
                ]),
            ],
            'ip already exists' => [
                'httpResponseDataCollection' => [
                    'droplets response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [
                                [
                                    'id' => 123,
                                ],
                            ],
                        ]),
                    ],
                    'ip find response' => [
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
                ],
                'expectedExitCode' => IpCreateCommand::EXIT_CODE_HAS_IP,
                'expectedOutput' => (string) json_encode([
                    'error' => [
                        'id' => 'has-ip',
                        'message' => 'Cannot create new IP, 127.0.0.200 already in use',
                    ],
                ]),
            ],
            'created' => [
                'httpResponseDataCollection' => [
                    'droplets response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [
                                [
                                    'id' => 123,
                                ],
                            ],
                        ]),
                    ],
                    'ip find response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'floating_ips' => [],
                        ]),
                    ],
                    'ip create response' => [
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
                    'droplet find response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplet' => [
                                'id' => 123,
                                'networks' => [
                                    'v4' => [
                                        [
                                            'ip_address' => '127.0.0.100',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ],
                ],
                'expectedExitCode' => IpCreateCommand::SUCCESS,
                'expectedOutput' => (string) json_encode([
                    'success' => [
                        'id' => 'created',
                        'message' => 'Assigned 127.0.0.100 to instance 123',
                        'context' => [
                            'ip' => '127.0.0.100',
                            'target-instance' => 123,
                        ],
                    ],
                ]),
            ],
        ];
    }
}
