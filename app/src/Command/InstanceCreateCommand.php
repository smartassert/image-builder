<?php

namespace App\Command;

use App\Services\CommandExceptionRenderer;
use App\Services\InstanceCreator;
use DigitalOceanV2\Exception\ExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: InstanceCreateCommand::NAME,
    description: 'Create a worker manager instance.',
)]
class InstanceCreateCommand extends Command
{
    public const NAME = 'app:instance:create';
    public const OPTION_OUTPUT_TEMPLATE = 'output-template';
    public const DEFAULT_OUTPUT_TEMPLATE = '{{ id }}';

    public function __construct(
        private InstanceCreator $instanceCreator,
        private CommandExceptionRenderer $commandExceptionRenderer,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_OUTPUT_TEMPLATE,
                null,
                InputOption::VALUE_REQUIRED,
                'Template into which to render the output. Allowed placeholders:' . "\n" .
                '{{ id }} - id of created instance' . "\n",
                self::DEFAULT_OUTPUT_TEMPLATE
            )
        ;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $instance = $this->instanceCreator->create();
        } catch (ExceptionInterface $e) {
            $io = new SymfonyStyle($input, $output);
            $io->error($this->commandExceptionRenderer->render($e));

            throw $e;
        }

        $outputTemplate = $input->getOption(self::OPTION_OUTPUT_TEMPLATE);
        $outputTemplate = is_string($outputTemplate) ? $outputTemplate : self::DEFAULT_OUTPUT_TEMPLATE;

        $output->write(str_replace('{{ id }}', (string) $instance->getId(), $outputTemplate));

        return Command::SUCCESS;
    }
}
