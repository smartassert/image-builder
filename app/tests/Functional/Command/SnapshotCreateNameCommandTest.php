<?php

namespace App\Tests\Functional\Command;

use App\Command\SnapshotCreateNameCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SnapshotCreateNameCommandTest extends KernelTestCase
{
    /**
     * @dataProvider executeDataProvider
     *
     * @param array<mixed> $input
     */
    public function testExecute(array $input, int $expectedReturnCode, string $expectedOutput): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find(SnapshotCreateNameCommand::NAME);
        self::assertInstanceOf(Command::class, $command);

        $commandTester = new CommandTester($command);
        $commandReturnCode = $commandTester->execute($input);

        self::assertSame($expectedReturnCode, $commandReturnCode);
        self::assertSame($expectedOutput, $commandTester->getDisplay());
    }

    /**
     * @return array<mixed>
     */
    public function executeDataProvider(): array
    {
        return [
            'no input' => [
                'input' => [],
                'expectedReturnCode' => Command::FAILURE,
                'expectedOutput' => '',
            ],
            'push' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'push',
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'master',
            ],
            'pull request, integer pull request number' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'pull_request',
                    '--' . SnapshotCreateNameCommand::OPTION_PULL_REQUEST_NUMBER => 29,
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'pull-request-29',
            ],
            'pull request, string pull request number' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'pull_request',
                    '--' . SnapshotCreateNameCommand::OPTION_PULL_REQUEST_NUMBER => '30',
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'pull-request-30',
            ],
            'release, float version' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'release',
                    '--' . SnapshotCreateNameCommand::OPTION_RELEASE_VERSION => 0.123,
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'release-0.123',
            ],
            'release, string version unquoted' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'release',
                    '--' . SnapshotCreateNameCommand::OPTION_RELEASE_VERSION => '0.456',
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'release-0.456',
            ],
            'release, string version quoted' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'release',
                    '--' . SnapshotCreateNameCommand::OPTION_RELEASE_VERSION => '"0.789"',
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'release-0.789',
            ],
        ];
    }
}
