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
        self::assertSame($expectedOutput, $output->fetch());
    }

    /**
     * @return array<mixed>
     */
    public function executeDataProvider(): array
    {
        return [
            'no instances, no template' => [
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
                'expectedOutput' => '',
            ],
            'single instance, no template' => [
                'input' => [],
                'httpResponseDataCollection' => [
                    'droplets' => [
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
                    'droplet version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.1',
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => '123: 0.1',
            ],
            'many instances, no template' => [
                'input' => [],
                'httpResponseDataCollection' => [
                    'droplets' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
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
                    ],
                    'droplet 123 version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.1',
                    ],
                    'droplet 456 version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.2',
                    ],
                    'droplet 789 version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.3',
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => '123: 0.1' . "\n" .
                    '456: 0.2' . "\n" .
                    '789: 0.3',
            ],
            'many instances, template to set github action step output' => [
                'input' => [
                    '--output-template' => '::set-output name={{ id }}::{{ version }}',
                ],
                'httpResponseDataCollection' => [
                    'droplets' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_HEADERS => [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        HttpResponseFactory::KEY_BODY => (string) json_encode([
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
                    ],
                    'droplet 123 version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.1',
                    ],
                    'droplet 456 version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.2',
                    ],
                    'droplet 789 version' => [
                        HttpResponseFactory::KEY_STATUS_CODE => 200,
                        HttpResponseFactory::KEY_BODY => '0.3',
                    ],
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => '::set-output name=123::0.1' . "\n" .
                    '::set-output name=456::0.2' . "\n" .
                    '::set-output name=789::0.3'
            ],
        ];
    }
}
