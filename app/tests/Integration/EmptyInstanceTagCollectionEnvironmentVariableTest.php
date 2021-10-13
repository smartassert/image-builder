<?php

namespace App\Tests\Integration;

use App\Command\InstanceListCommand;
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
            InstanceListCommand::NAME => [
                'command' => InstanceListCommand::NAME,
            ],
        ];
    }
}
