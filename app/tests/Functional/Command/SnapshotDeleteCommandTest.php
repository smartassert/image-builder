<?php

namespace App\Tests\Functional\Command;

use App\Command\SnapshotDeleteCommand;
use App\Tests\Services\HttpResponseFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SnapshotDeleteCommandTest extends KernelTestCase
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

        $command = self::getContainer()->get(SnapshotDeleteCommand::class);
        \assert($command instanceof SnapshotDeleteCommand);

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
        return [
            'invalid api token' => [
                'httpResponseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 401,
                ],
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::INVALID,
            ],
            'not exists' => [
                'httpResponseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 404,
                ],
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'success' => [
                'httpResponseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 202,
                ],
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
        ];
    }
}
