<?php

namespace App\Tests\Functional\Command;

use App\Command\InstanceListCommand;
use App\Tests\Services\HttpResponseFactory;
use DigitalOceanV2\Exception\RuntimeException;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class InstanceListCommandTest extends KernelTestCase
{
    private InstanceListCommand $command;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $command = self::getContainer()->get(InstanceListCommand::class);
        \assert($command instanceof InstanceListCommand);
        $this->command = $command;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider executeThrowsExceptionDataProvider
     *
     * @param array<mixed>             $responseData
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testExecuteThrowsException(
        array $responseData,
        string $expectedExceptionClass,
        string $expectedExceptionMessage,
        int $expectedExceptionCode
    ): void {
        $this->mockHandler->append($this->httpResponseFactory->createFromArray($responseData));

        self::expectException($expectedExceptionClass);
        self::expectExceptionMessage($expectedExceptionMessage);
        self::expectExceptionCode($expectedExceptionCode);

        $this->command->run(new ArrayInput([]), new BufferedOutput());
    }

    /**
     * @return array<mixed>
     */
    public function executeThrowsExceptionDataProvider(): array
    {
        return [
            'invalid api token' => [
                'responseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 401,
                ],
                'expectedExceptionClass' => RuntimeException::class,
                'expectedExceptionMessage' => 'Unauthorized',
                'expectedExceptionCode' => 401,
            ],
        ];
    }

    /**
     * @dataProvider executeDataProvider
     *
     * @param array<mixed> $input
     * @param array<mixed> $httpResponseDataCollection
     */
    public function testExecuteSuccess(
        array $input,
        array $httpResponseDataCollection,
        int $expectedReturnCode,
        string $expectedOutput
    ): void {
        foreach ($httpResponseDataCollection as $httpResponseData) {
            $this->mockHandler->append($this->httpResponseFactory->createFromArray($httpResponseData));
        }

        $output = new BufferedOutput();

        $commandReturnCode = $this->command->run(new ArrayInput($input), $output);

        self::assertSame($expectedReturnCode, $commandReturnCode);
        self::assertJsonStringEqualsJsonString($expectedOutput, $output->fetch());
    }

    /**
     * @return array<mixed>
     */
    public function executeDataProvider(): array
    {
        $dropletData = [
            [
                'id' => 123,
            ],
            [
                'id' => 456,
            ],
            [
                'id' => 789,
            ],
        ];

        $instanceResponseData = [
            [
                'version' => '0.1',
                'message-queue-size' => 3,
            ],
            [
                'version' => '0.2',
                'message-queue-size' => 7,
            ],
            [
                'version' => '0.3',
                'message-queue-size' => 0,
            ]
        ];

        return [
            'no instances' => [
                'input' => [],
                'httpResponseDataCollection' => [
                    'droplets' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [],
                        ]),
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => '[]',
            ],
            'single instance' => [
                'input' => [],
                'httpResponseDataCollection' => [
                    'droplets' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => [
                                $dropletData[0],
                            ],
                        ]),
                    ],
                    'droplet status' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[0]),
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => (string) json_encode([
                    [
                        'id' => 123,
                        'version' => '0.1',
                        'message-queue-size' => 3,
                    ],
                ]),
            ],
            'many instances, no filter' => [
                'input' => [],
                'httpResponseDataCollection' => [
                    'droplets' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => $dropletData,
                        ]),
                    ],
                    'droplet 123' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[0]),
                    ],
                    'droplet 456' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[1]),
                    ],
                    'droplet 789' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[2]),
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => (string) json_encode([
                    [
                        'id' => 123,
                        'version' => '0.1',
                        'message-queue-size' => 3,
                    ],
                    [
                        'id' => 456,
                        'version' => '0.2',
                        'message-queue-size' => 7,
                    ],
                    [
                        'id' => 789,
                        'version' => '0.3',
                        'message-queue-size' => 0,
                    ],
                ]),
            ],
            'many instances, --with-empty-message-queue-size' => [
                'input' => [
                    '--with-empty-message-queue' => true,
                ],
                'httpResponseDataCollection' => [
                    'droplets' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
                            'droplets' => $dropletData,
                        ]),
                    ],
                    'droplet 123' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[0]),
                    ],
                    'droplet 456' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[1]),
                    ],
                    'droplet 789' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => json_encode($instanceResponseData[2]),
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => (string) json_encode([
                    [
                        'id' => 789,
                        'version' => '0.3',
                        'message-queue-size' => 0,
                    ],
                ]),
            ],
        ];
    }
}
