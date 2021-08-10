<?php

namespace App\Command;

use App\Services\CommandOutputHandler;
use App\Services\InstanceRepository;
use DigitalOceanV2\Exception\ExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: InstanceCreateCommand::NAME,
    description: 'Create a worker manager instance.',
)]
class InstanceCreateCommand extends Command
{
    public const NAME = 'app:instance:create';

    public function __construct(
        private InstanceRepository $instanceRepository,
        private CommandOutputHandler $commandOutputHandler,
    ) {
        parent::__construct(null);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commandOutputHandler->setOutput($output);

        $instance = $this->instanceRepository->findCurrent();
        if (null === $instance) {
            $instance = $this->instanceRepository->create();
        }

        $this->commandOutputHandler->createSuccessOutput(['id' => $instance->getId()]);

        return Command::SUCCESS;
    }
}
