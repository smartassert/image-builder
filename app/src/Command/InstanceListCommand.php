<?php

namespace App\Command;

use App\Model\InstanceCollection;
use App\Model\InstanceMatcher\InstanceEmptyMessageQueueMatcher;
use App\Model\InstanceMatcher\InstanceNotHasIpMatcher;
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
    public const OPTION_WITH_EMPTY_MESSAGE_QUEUE = 'with-empty-message-queue';
    public const OPTION_WITHOUT_IP = 'without-ip';

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
                self::OPTION_WITH_EMPTY_MESSAGE_QUEUE,
                null,
                InputOption::VALUE_NONE,
                'Include only instances with an empty message queue'
            )
            ->addOption(
                self::OPTION_WITHOUT_IP,
                null,
                InputOption::VALUE_OPTIONAL,
                'Include only instances without a specific IP'
            )
        ;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $withEmptyMessageQueue = $input->getOption(self::OPTION_WITH_EMPTY_MESSAGE_QUEUE);
        if (!is_bool($withEmptyMessageQueue)) {
            $withEmptyMessageQueue = false;
        }

        $withoutIp = $input->getOption(self::OPTION_WITHOUT_IP);
        if (!is_string($withoutIp)) {
            $withoutIp = null;
        }

        try {
            $instances = $this->findInstances($withEmptyMessageQueue, $withoutIp);
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

        $prettyPrint = $input->getOption(self::OPTION_WITH_EMPTY_MESSAGE_QUEUE);
        $prettyPrint = is_scalar($prettyPrint) && $prettyPrint;
        $jsonEncodeFlags = $prettyPrint ? JSON_PRETTY_PRINT : 0;

        $output->write((string) json_encode($collectionData, $jsonEncodeFlags));

        return Command::SUCCESS;
    }

    /**
     * @throws ExceptionInterface
     */
    private function findInstances(
        bool $withEmptyMessageQueue = false,
        ?string $withoutIp = null,
    ): InstanceCollection {
        $instances = $this->instanceRepository->findAll();
        $instances = $this->instanceCollectionHydrator->hydrate($instances);

        if (true === $withEmptyMessageQueue) {
            $instances = $instances->filter(new InstanceEmptyMessageQueueMatcher());
        }

        if (is_string($withoutIp)) {
            $instances = $instances->filter(new InstanceNotHasIpMatcher($withoutIp));
        }

        return $instances;
    }
}
