<?php

namespace App\Tests\Functional\Command;

use App\Command\SnapshotExistsCommand;
use App\Tests\Services\HttpResponseFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SnapshotExistsCommandTest extends KernelTestCase
{
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider executeDataProvider
     *
     * @param array<mixed> $httpResponseData
     * @param array<mixed> $input
     */
    public function testExecute(array $httpResponseData, array $input, int $expectedReturnCode): void
    {
        $this->mockHandler->append(
            $this->httpResponseFactory->createFromArray($httpResponseData)
        );

        $command = self::getContainer()->get(SnapshotExistsCommand::class);
        \assert($command instanceof SnapshotExistsCommand);

        $input = new ArrayInput($input);
        $output = new BufferedOutput();

        $commandReturnCode = $command->run($input, $output);

        self::assertSame($expectedReturnCode, $commandReturnCode);
    }

    /**
     * @return array<mixed>
     */
    public function executeDataProvider(): array
    {
        $notExistsResponseData = [
            HttpResponseFactory::KEY_STATUS_CODE => 404,
        ];

        $existsResponseData = [
            HttpResponseFactory::KEY_STATUS_CODE => 200,
            HttpResponseFactory::KEY_HEADERS => [
                'content-type' => 'application/json; charset=utf-8',
            ],
            HttpResponseFactory::KEY_BODY => (string) json_encode([
                'snapshot' => [],
            ]),
        ];

        return [
            'not exists, expect exists (default)' => [
                'httpResponseData' => $notExistsResponseData,
                'input' => [],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'not exists, expect not exists as false' => [
                'httpResponseData' => $notExistsResponseData,
                'input' => [
                    '--expect-exists' => false,
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'not exists, expect not exists as zero' => [
                'httpResponseData' => $notExistsResponseData,
                'input' => [
                    '--expect-exists' => 0,
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'not exists, expect not exists as quoted zero' => [
                'httpResponseData' => $notExistsResponseData,
                'input' => [
                    '--expect-exists' => '0',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'exists, expect exists (default)' => [
                'httpResponseData' => $existsResponseData,
                'input' => [],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'exists, expect not exists as false' => [
                'httpResponseData' => $existsResponseData,
                'input' => [
                    '--expect-exists' => false,
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'exists, expect not exists as zero' => [
                'httpResponseData' => $existsResponseData,
                'input' => [
                    '--expect-exists' => 0,
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'exists, expect not exists as quoted zero' => [
                'httpResponseData' => $existsResponseData,
                'input' => [
                    '--expect-exists' => '0',
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
        ];
    }
}
