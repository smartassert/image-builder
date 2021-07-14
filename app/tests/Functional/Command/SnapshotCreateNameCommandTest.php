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
     */
    public function testExecute(array $input, int $expectedReturnCode, string $expectedOutput)
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
            'pull request' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'pull-request',
                    '--' . SnapshotCreateNameCommand::OPTION_PULL_REQUEST_NUMBER => 29,
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'pull-request-29',
            ],
            'release, unquoted version' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'release',
                    '--' . SnapshotCreateNameCommand::OPTION_RELEASE_VERSION => 0.123,
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'release-0.123',
            ],
            'release, quoted version' => [
                'input' => [
                    '--' . SnapshotCreateNameCommand::OPTION_EVENT_NAME => 'release',
                    '--' . SnapshotCreateNameCommand::OPTION_RELEASE_VERSION => '"0.456"',
                ],
                'expectedReturnCode' => Command::SUCCESS,
                'expectedOutput' => 'release-0.456',
            ],
        ];
    }
}
