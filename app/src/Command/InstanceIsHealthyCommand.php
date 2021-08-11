<?php

namespace App\Command;

use App\Services\CommandOutputHandler;
use App\Services\InstanceClient;
use App\Services\InstanceRepository;
use DigitalOceanV2\Exception\ExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: InstanceIsHealthyCommand::NAME,
    description: 'Perform instance health check',
)]
class InstanceIsHealthyCommand extends AbstractInstanceActionCommand
{
    public const NAME = 'app:instance:is-healthy';
    public const EXIT_CODE_ID_INVALID = 3;
    public const EXIT_CODE_NOT_FOUND = 4;

    public function __construct(
        InstanceRepository $instanceRepository,
        private InstanceClient $instanceClient,
        private CommandOutputHandler $outputHandler,
    ) {
        parent::__construct($instanceRepository);
    }

    /**
     * @throws ExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->outputHandler->setOutput($output);

        $id = $this->getId();
        if (null === $id) {
            $this->outputHandler->createErrorOutput('id-invalid');

            return self::EXIT_CODE_ID_INVALID;
        }

        $instance = $this->instanceRepository->find($id);
        if (null === $instance) {
            $this->outputHandler->createErrorOutput('not-found', ['id' => $id]);

            return self::EXIT_CODE_NOT_FOUND;
        }

        $health = $this->instanceClient->getHealth($instance);

        $this->outputHandler->createSuccessOutput([
            'is-healthy' => $health->isAvailable(),
            'services' => $health->jsonSerialize(),
        ]);

        return $health->isAvailable() ? Command::SUCCESS : Command::FAILURE;
    }
}
