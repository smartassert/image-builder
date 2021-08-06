<?php

namespace App\Command;

use App\Services\InstanceRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: self::NAME,
    description: 'Destroy an instance.',
)]
class InstanceDestroyCommand extends Command
{
    public const NAME = 'app:instance:destroy';

    private const OPTION_ID = 'id';

    public function __construct(
        private InstanceRepository $instanceRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'ID of the instance to destroy'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $this->getIdFromInput($input);
        if (is_int($id)) {
            $this->instanceRepository->delete($id);
        }

        return Command::SUCCESS;
    }

    private function getIdFromInput(InputInterface $input): ?int
    {
        $id = $input->getOption(self::OPTION_ID);

        return ctype_digit($id) ? (int) $id : null;
    }
}
