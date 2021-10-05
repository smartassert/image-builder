<?php

namespace App\Command;

use App\Model\Filter;
use App\Model\InstanceCollection;
use App\Services\FilterStringParser;
use App\Services\InstanceCollectionHydrator;
use App\Services\InstanceRepository;
use DigitalOceanV2\Exception\ExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: InstanceListCommand::NAME,
    description: 'List instances',
)]
class InstanceListCommand extends Command
{
    public const NAME = 'app:instance:list';
    public const OPTION_FILTER = 'filter';

    public function __construct(
        private InstanceRepository $instanceRepository,
        private InstanceCollectionHydrator $instanceCollectionHydrator,
        private FilterStringParser $filterStringParser,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_FILTER,
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter'
            )
        ;
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filterString = $input->getOption('filter');
        $filters = is_string($filterString)
            ? $this->filterStringParser->parse($filterString)
            : [];

        $instances = $this->findInstances($filters);

        $collectionData = [];

        foreach ($instances as $instance) {
            $collectionData[] = [
                'id' => $instance->getId(),
                'version' => $instance->getVersion(),
                'message-queue-size' => $instance->getMessageQueueSize(),
            ];
        }

        $output->write((string) json_encode([
            'instances' => $collectionData,
        ]));

        return Command::SUCCESS;
    }

    /**
     * @param Filter[] $filters
     *
     * @throws ExceptionInterface
     */
    private function findInstances(array $filters): InstanceCollection
    {
        $instances = $this->instanceRepository->findAll();
        $instances = $this->instanceCollectionHydrator->hydrate($instances);

        foreach ($filters as $filter) {
            $instances = $instances->filter($filter);
        }

        return $instances;
    }
}
