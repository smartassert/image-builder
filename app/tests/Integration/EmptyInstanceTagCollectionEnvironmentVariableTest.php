<?php

namespace App\Tests\Integration;

use App\Command\InstanceCreateCommand;
use App\Command\InstanceDestroyCommand;
use App\Command\InstanceIsHealthyCommand;
use App\Command\InstanceListCommand;
use App\Command\IpAssignCommand;
use App\Command\IpCreateCommand;
use App\Command\IpGetCommand;
use App\Exception\EmptyEnvironmentVariableException;
use PHPUnit\Framework\TestCase;

class EmptyInstanceTagCollectionEnvironmentVariableTest extends TestCase
{
    /**
     * @dataProvider commandDataProvider
     */
    public function testExecuteCommandWithInstanceTagCollectionEmpty(string $command): void
    {
        $shellCommand = 'INSTANCE_COLLECTION_TAG= php bin/console ' . $command . ' --env=test';
        $output = (string) shell_exec($shellCommand);

        $expectedContainedExceptionName = str_replace('\\', '\\\\', EmptyEnvironmentVariableException::class);
        self::assertStringContainsString($expectedContainedExceptionName, $output);

        $expectedContainedExceptionMessage = str_replace(
            '"',
            '\"',
            'Environment variable "INSTANCE_COLLECTION_TAG" is not allowed to be empty'
        );

        self::assertStringContainsString($expectedContainedExceptionMessage, $output);
    }

    /**
     * @dataProvider commandDataProvider
     */
    public function testExecuteCommandWithInstanceTagCollectionNotEmpty(string $command): void
    {
        $shellCommand = 'INSTANCE_COLLECTION_TAG=non-empty php bin/console ' . $command . ' --env=test';
        $output = (string) shell_exec($shellCommand);

        $expectedContainedExceptionName = str_replace('\\', '\\\\', EmptyEnvironmentVariableException::class);
        self::assertStringNotContainsString($expectedContainedExceptionName, $output);

        $expectedContainedExceptionMessage = str_replace(
            '"',
            '\"',
            'Environment variable "INSTANCE_COLLECTION_TAG" is not allowed to be empty'
        );

        self::assertStringNotContainsString($expectedContainedExceptionMessage, $output);
    }

    /**
     * @return array<mixed>
     */
    public function commandDataProvider(): array
    {
        return [
            InstanceCreateCommand::NAME => [
                'command' => InstanceCreateCommand::NAME,
            ],
            InstanceDestroyCommand::NAME => [
                'command' => InstanceDestroyCommand::NAME,
            ],
            InstanceIsHealthyCommand::NAME => [
                'command' => InstanceIsHealthyCommand::NAME,
            ],
            InstanceListCommand::NAME => [
                'command' => InstanceListCommand::NAME,
            ],
            IpAssignCommand::NAME => [
                'command' => IpAssignCommand::NAME,
            ],
            IpCreateCommand::NAME => [
                'command' => IpCreateCommand::NAME,
            ],
            IpGetCommand::NAME => [
                'command' => IpGetCommand::NAME,
            ],
        ];
    }
}