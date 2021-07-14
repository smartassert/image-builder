<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: SnapshotCreateNameCommand::NAME,
    description: 'Create a snapshot name',
)]
class SnapshotCreateNameCommand extends Command
{
    public const NAME = 'app:snapshot:create-name';
    public const OPTION_EVENT_NAME = 'event-name';
    public const OPTION_PULL_REQUEST_NUMBER = 'pull-request-number';
    public const OPTION_RELEASE_VERSION = 'release-version';

    private const EVENT_NAME_PUSH = 'push';
    private const EVENT_NAME_PULL_REQUEST = 'pull-request';
    private const EVENT_NAME_PULL_RELEASE = 'release';
    private const NAME_DEFAULT = 'master';
    private const NAME_PULL_REQUEST = 'pull-request-%s';
    private const NAME_RELEASE = 'release-%s';

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_EVENT_NAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'Github event name'
            )
            ->addOption(
                self::OPTION_PULL_REQUEST_NUMBER,
                null,
                InputOption::VALUE_OPTIONAL,
                'Github pull request number'
            )
            ->addOption(
                self::OPTION_RELEASE_VERSION,
                null,
                InputOption::VALUE_OPTIONAL,
                'Release version'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $eventName = $input->getOption('event-name');

        if (self::EVENT_NAME_PUSH === $eventName) {
            $output->write(self::NAME_DEFAULT);

            return Command::SUCCESS;
        }

        if (self::EVENT_NAME_PULL_REQUEST === $eventName) {
            $pullRequestNumber = $input->getOption('pull-request-number');
            $pullRequestNumber = is_scalar($pullRequestNumber) ? (string) $pullRequestNumber : null;

            $output->write(sprintf(self::NAME_PULL_REQUEST, $pullRequestNumber));

            return Command::SUCCESS;
        }

        if (self::EVENT_NAME_PULL_RELEASE === $eventName) {
            $releaseVersion = $input->getOption('release-version');
            $releaseVersion = is_scalar($releaseVersion) ? (string) $releaseVersion : '';
            $releaseVersion = str_replace('"', '', $releaseVersion);

            $output->write(sprintf(self::NAME_RELEASE, $releaseVersion));

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
