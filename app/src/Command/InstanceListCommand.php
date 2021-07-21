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
    public const OPTION_OUTPUT_TEMPLATE = 'output-template';
    public const DEFAULT_OUTPUT_TEMPLATE = '{{ instance-json }}';

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
                self::OPTION_OUTPUT_TEMPLATE,
                null,
                InputOption::VALUE_REQUIRED,
                'Template (per instance) into which to render the output. Allowed placeholders:' . "\n" .
                '{{ id }} - instance id' . "\n" .
                '{{ version }} - instance version' . "\n",
                self::DEFAULT_OUTPUT_TEMPLATE
            )
        ;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputTemplate = $input->getOption(self::OPTION_OUTPUT_TEMPLATE);
        $outputTemplate = is_string($outputTemplate) ? $outputTemplate : self::DEFAULT_OUTPUT_TEMPLATE;

        try {
            $instances = $this->instanceRepository->findAll();
            $instances = $this->instanceCollectionHydrator->hydrate($instances);
        } catch (ExceptionInterface $e) {
            $io = new SymfonyStyle($input, $output);
            $io->error($this->commandExceptionRenderer->render($e));

            throw $e;
        }

        $instancesCount = count($instances);

        foreach ($instances as $instanceIndex => $instance) {
            $output->write(
                str_replace(
                    '{{ instance-json }}',
                    (string) json_encode([
                        'id' => $instance->getId(),
                        'version' => $instance->getVersion(),
                        'message-queue-size' => $instance->getMessageQueueSize(),
                    ]),
                    $outputTemplate
                ),
                $instanceIndex < ($instancesCount - 1)
            );
        }

        return Command::SUCCESS;
    }
}
