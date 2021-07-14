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
     * @param array<ResponseInterface> $httpFixtures
     * @param array<mixed>             $input
     */
    public function testExecute(array $httpFixtures, array $input, int $expectedReturnCode): void
    {
        $container = self::getContainer();
        $mockHandler = $container->get(MockHandler::class);
        if ($mockHandler instanceof MockHandler) {
            $mockHandler->append(...$httpFixtures);
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
        return [
            'invalid api token' => [
                'httpFixtures' => [
                    new Response(401),
                ],
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::INVALID,
            ],
            'snapshot not exists' => [
                'httpFixtures' => [
                    new Response(404),
                ],
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::FAILURE,
            ],
            'snapshot exists' => [
                'httpFixtures' => [
                    new Response(
                        200,
                        [
                            'content-type' => 'application/json; charset=utf-8',
                        ],
                        (string) json_encode([
                            'snapshot' => [],
                        ])
                    ),
                ],
                'input' => [
                    '--id' => '123',
                ],
                'expectedReturnCode' => Command::SUCCESS,
            ],
        ];
    }
}
