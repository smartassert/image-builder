<?php

namespace App\Command;

use DigitalOceanV2\Api\Snapshot as SnapshotApi;
use DigitalOceanV2\Exception\ExceptionInterface;
use DigitalOceanV2\Exception\RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: SnapshotExistsCommand::NAME,
    description: 'Verify that a snapshot exists. Returns 0 if exists, 1 if not exists and 2 if an error occurs.',
)]
class SnapshotExistsCommand extends Command
{
    public const NAME = 'app:snapshot:exists';
    public const OPTION_ID = 'id';

    public function __construct(
        private SnapshotApi $snapshotApi,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption(self::OPTION_ID, null, InputOption::VALUE_REQUIRED, 'Snapshot ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getOption(self::OPTION_ID);
        $id = is_string($id) ? $id : 'invalid';

        try {
            $this->snapshotApi->getById($id);

            return Command::SUCCESS;
        } catch (RuntimeException $runtimeException) {
            if (404 === $runtimeException->getCode()) {
                return Command::FAILURE;
            }

            $exception = $runtimeException;
        } catch (ExceptionInterface $vendorException) {
            $exception = $vendorException;
        }

        if ($exception instanceof \Throwable) {
            $exceptionMessage = sprintf(
                '%s %s: %s',
                $exception::class,
                $exception->getCode(),
                $exception->getMessage()
            );

            $io = new SymfonyStyle($input, $output);
            $io->error($exceptionMessage);
        }

        return Command::INVALID;
    }
}
