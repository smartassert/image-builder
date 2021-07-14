<?php

namespace App\Tests\Functional\Command;

use App\Command\SnapshotDeleteCommand;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SnapshotDeleteCommandTest extends KernelTestCase
{
    /**
     * @dataProvider executeDataProvider
     *
     * @param array<mixed> $input
     */
    public function testExecute(ResponseInterface $httpResponse, array $input, int $expectedReturnCode): void
    {
        $container = self::getContainer();
        $mockHandler = $container->get(MockHandler::class);
        if ($mockHandler instanceof MockHandler) {
            $mockHandler->append($httpResponse);
        }

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
        $successResponse = new Response(204);
        $notExistsResponse = new Response(404);

        return [
            'invalid api token' => [
                'response' => new Response(401),
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::INVALID,
            ],
            'not exists' => [
                'response' => $notExistsResponse,
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
            'success' => [
                'response' => $successResponse,
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
        ];
    }
}
