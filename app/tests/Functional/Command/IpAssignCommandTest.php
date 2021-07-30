<?php

namespace App\Tests\Functional\Command;

use App\Command\IpAssignCommand;
use App\Exception\ActionTimeoutException;
use App\Services\ActionRunner;
use App\Tests\Services\HttpResponseFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use webignition\ObjectReflector\ObjectReflector;

class IpAssignCommandTest extends KernelTestCase
{
    private IpAssignCommand $command;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $command = self::getContainer()->get(IpAssignCommand::class);
        \assert($command instanceof IpAssignCommand);
        $this->command = $command;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider runSuccessDataProvider
     *
     * @param array<mixed> $httpResponseDataCollection
     */
    public function testRunSuccess(
        ?callable $setup,
        array $httpResponseDataCollection,
        int $expectedExitCode,
        string $expectedOutput
    ): void {
        if (is_callable($setup)) {
            $setup($this->command);
        }

        foreach ($httpResponseDataCollection as $httpResponseData) {
            $this->mockHandler->append(
                $this->httpResponseFactory->createFromArray($httpResponseData)
            );
        }

        $output = new BufferedOutput();

        $exitCode = $this->command->run(new ArrayInput([]), $output);

        self::assertSame($expectedExitCode, $exitCode);
        self::assertJsonStringEqualsJsonString($expectedOutput, $output->fetch());
    }

    /**
     * @return array<mixed>
     */
    public function runSuccessDataProvider(): array
    {
        return [
            'no current instance' => [
                'setup' => null,
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
                'expectedExitCode' => IpAssignCommand::EXIT_CODE_NO_CURRENT_INSTANCE,
                'expectedOutput' => (string) json_encode([
                    'error' => [
                        'id' => 'no-instance',
                        'message' => 'Cannot re-assign IP, no current instance found'
                    ],
                ]),
            ],
            'no ip' => [
                'setup' => null,
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
                ],
                'expectedExitCode' => IpAssignCommand::EXIT_CODE_NO_IP,
                'expectedOutput' => (string) json_encode([
                    'error' => [
                        'id' => 'no-ip',
                        'message' => 'Cannot re-assign IP, none found'
                    ],
                ]),
            ],
            'ip already assigned to current instance' => [
                'setup' => null,
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
                                    'networks' => [
                                        'v4' => [
                                            [
                                                'ip_address' => '127.0.0.200',
                                            ],
                                        ],
                                    ],
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
                'expectedExitCode' => Command::SUCCESS,
                'expectedOutput' => (string) json_encode([
                    'success' => [
                        'id' => 'already-assigned',
                        'message' => '127.0.0.200 is already assigned to instance 123',
                        'context' => [
                            'ip' => '127.0.0.200',
                            'source-instance' => 123,
                            'target-instance' => 123,
                        ],
                    ],
                ]),
            ],
            'ip re-assigned' => [
                'setup' => null,
                'httpResponseDataCollection' => [
                    'droplets response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [
                                [
                                    'id' => 456,
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
                    're-assign response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'action' => [
                                'id' => 789,
                                'type' => 'assign_ip',
                                'status' => 'in-progress',
                            ],
                        ]),
                    ],
                    'action status check response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'action' => [
                                'id' => 789,
                                'type' => 'assign_ip',
                                'status' => 'completed',
                            ],
                        ]),
                    ],
                ],
                'expectedExitCode' => Command::SUCCESS,
                'expectedOutput' => (string) json_encode([
                    'success' => [
                        'id' => 're-assigned',
                        'message' => 'Re-assigned 127.0.0.200 from instance 123 to instance 456',
                        'context' => [
                            'ip' => '127.0.0.200',
                            'source-instance' => 123,
                            'target-instance' => 456,
                        ],
                    ],
                ]),
            ],
            'assignment timed out' => [
                'setup' => function (IpAssignCommand $command) {
                    $actionRunner = \Mockery::mock(ActionRunner::class);
                    $actionRunner
                        ->shouldReceive('run')
                        ->andThrow(new ActionTimeoutException())
                    ;

                    ObjectReflector::setProperty(
                        $command,
                        IpAssignCommand::class,
                        'actionRunner',
                        $actionRunner
                    );
                },
                'httpResponseDataCollection' => [
                    'droplets response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [
                                [
                                    'id' => 456,
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
                    're-assign response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'action' => [
                                'id' => 789,
                                'type' => 'assign_ip',
                                'status' => 'in-progress',
                            ],
                        ]),
                    ],
                    'action status check response' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'action' => [
                                'id' => 789,
                                'type' => 'assign_ip',
                                'status' => 'completed',
                            ],
                        ]),
                    ],
                ],
                'expectedExitCode' => IpAssignCommand::EXIT_CODE_ASSIGNMENT_TIMED_OUT,
                'expectedOutput' => (string) json_encode([
                    'error' => [
                        'id' => 'assignment-timed-out',
                        'message' => 'Waited 30 seconds to assign 127.0.0.200 from instance 123 to instance 456',
                        'context' => [
                            'ip' => '127.0.0.200',
                            'source-instance' => 123,
                            'target-instance' => 456,
                            'timeout-in-seconds' => 30,
                        ],
                    ],
                ]),
            ],
        ];
    }
}
