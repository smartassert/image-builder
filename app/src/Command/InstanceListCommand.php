<?php

namespace App\Command;

use App\Services\CommandExceptionRenderer;
use App\Services\InstanceCollectionHydrator;
use App\Services\InstanceRepository;
use DigitalOceanV2\Exception\ExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: InstanceListCommand::NAME,
    description: 'Create a worker manager instance.',
)]
class InstanceListCommand extends Command
{
    public const NAME = 'app:instance:list';
    public const OPTION_PRETTY_PRINT = 'pretty-print';

    public function __construct(
        private InstanceRepository $instanceRepository,
        private InstanceCollectionHydrator $instanceCollectionHydrator,
        private CommandExceptionRenderer $commandExceptionRenderer,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_PRETTY_PRINT,
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                false
            )
        ;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $instances = $this->instanceRepository->findAll();
            $instances = $this->instanceCollectionHydrator->hydrate($instances);
        } catch (ExceptionInterface $e) {
            $io = new SymfonyStyle($input, $output);
            $io->error($this->commandExceptionRenderer->render($e));

            throw $e;
        }

        $collectionData = [];

        foreach ($instances as $instance) {
            $collectionData[] = [
                'id' => $instance->getId(),
                'version' => $instance->getVersion(),
                'message-queue-size' => $instance->getMessageQueueSize(),
            ];
        }

        $prettyPrint = $input->getOption(self::OPTION_PRETTY_PRINT);
        $prettyPrint = is_scalar($prettyPrint) && $prettyPrint;
        $jsonEncodeFlags = $prettyPrint ? JSON_PRETTY_PRINT : 0;

        $output->write((string) json_encode($collectionData, $jsonEncodeFlags));

        return Command::SUCCESS;
    }
}
