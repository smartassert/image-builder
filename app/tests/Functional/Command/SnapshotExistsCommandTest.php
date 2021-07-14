<?php

namespace App\Tests\Functional\Command;

use App\Command\SnapshotExistsCommand;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SnapshotExistsCommandTest extends KernelTestCase
{
    /**
     * @dataProvider executeDataProvider
     *
     * @param array<mixed>             $input
     */
    public function testExecute(ResponseInterface $httpResponse, array $input, int $expectedReturnCode): void
    {
        $container = self::getContainer();
        $mockHandler = $container->get(MockHandler::class);
        if ($mockHandler instanceof MockHandler) {
            $mockHandler->append($httpResponse);
        }

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
        $notExistsResponse = new Response(404);
        $existsResponse = new Response(
            200,
            [
                'content-type' => 'application/json; charset=utf-8',
            ],
            (string) json_encode([
                'snapshot' => [],
            ])
        );

        return [
            'invalid api token' => [
                'response' => new Response(401),
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::INVALID,
            ],
            'not exists, expect exists (default)' => [
                'response' => $notExistsResponse,
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'not exists, expect not exists as false' => [
                'response' => $notExistsResponse,
                'input' => [
                    '--id' => '123',
                    '--expect-exists' => false,
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'not exists, expect not exists as zero' => [
                'response' => $notExistsResponse,
                'input' => [
                    '--id' => '123',
                    '--expect-exists' => 0,
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'not exists, expect not exists as quoted zero' => [
                'response' => $notExistsResponse,
                'input' => [
                    '--id' => '123',
                    '--expect-exists' => '0',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'exists, expect exists (default)' => [
                'response' => $existsResponse,
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'exists, expect not exists as false' => [
                'response' => $existsResponse,
                'input' => [
                    '--id' => '123',
                    '--expect-exists' => false,
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'exists, expect not exists as zero' => [
                'response' => $existsResponse,
                'input' => [
                    '--id' => '123',
                    '--expect-exists' => 0,
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'exists, expect not exists as quoted zero' => [
                'response' => $existsResponse,
                'input' => [
                    '--id' => '123',
                    '--expect-exists' => '0',
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
        ];
    }
}
