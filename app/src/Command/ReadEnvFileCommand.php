<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

#[AsCommand(
    name: ReadEnvFileCommand::NAME,
    description: 'Read the contents of an env file, output as a formatted string',
)]
class ReadEnvFileCommand extends Command
{
    public const NAME = 'app:env-file:read';
    public const OPTION_PATH = 'path';
    public const OPTION_OUTPUT_FORMAT = 'output-format';

    public const DEFAULT_OUTPUT_FORMAT = '{{ name }}={{ value }}';

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_PATH,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to env file'
            )
            ->addOption(
                self::OPTION_OUTPUT_FORMAT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to env file',
                self::DEFAULT_OUTPUT_FORMAT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentEnvVarNameList = (string) $_SERVER['SYMFONY_DOTENV_VARS'] ?? '';
        $path = $input->getOption(self::OPTION_PATH);
        $path = is_string($path) ? $path : '';

        (new Dotenv())->load($path);

        $envVarNameList = (string) $_SERVER['SYMFONY_DOTENV_VARS'] ?? '';
        $loadedEnvVarNameList = str_replace($currentEnvVarNameList, '', $envVarNameList);
        $loadedEnvVarNameList = ltrim($loadedEnvVarNameList, ',');
        $loadedEnvVarNames = explode(',', $loadedEnvVarNameList);

        $outputFormat = $input->getOption(self::OPTION_OUTPUT_FORMAT);
        $outputFormat = is_string($outputFormat) ? $outputFormat : self::DEFAULT_OUTPUT_FORMAT;

        foreach ($loadedEnvVarNames as $envVarName) {
            $output->writeln(str_replace(
                [
                    '{{ name }}',
                    '{{ value }}',
                ],
                [
                    $envVarName,
                    $_SERVER[$envVarName],
                ],
                $outputFormat
            ));
        }

        return Command::SUCCESS;
    }
}
