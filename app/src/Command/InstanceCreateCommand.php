<?php

namespace App\Command;

use App\Services\CommandExceptionRenderer;
use App\Services\InstanceRepository;
use DigitalOceanV2\Exception\ExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: InstanceCreateCommand::NAME,
    description: 'Create a worker manager instance.',
)]
class InstanceCreateCommand extends Command
{
    public const NAME = 'app:instance:create';

    public function __construct(
        private InstanceRepository $instanceRepository,
        private CommandExceptionRenderer $commandExceptionRenderer,
    ) {
        parent::__construct(null);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $instance = $this->instanceRepository->findCurrent();
            if (null === $instance) {
                $instance = $this->instanceRepository->create();
            }
        } catch (ExceptionInterface $e) {
            $io = new SymfonyStyle($input, $output);
            $io->error($this->commandExceptionRenderer->render($e));

            throw $e;
        }

        $output->write((string) $instance->getId());

        return Command::SUCCESS;
    }
}
