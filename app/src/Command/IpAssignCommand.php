<?php

namespace App\Command;

use App\ActionHandler\ActionHandler;
use App\Services\ActionRepository;
use App\Services\ActionRunner;
use App\Services\FloatingIpManager;
use App\Services\FloatingIpRepository;
use App\Services\InstanceRepository;
use DigitalOceanV2\Entity\Action as ActionEntity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: IpAssignCommand::NAME,
    description: 'Add a short description for your command',
)]
class IpAssignCommand extends Command
{
    public const NAME = 'app:ip:assign';
    public const EXIT_CODE_NO_CURRENT_INSTANCE = 3;
    public const EXIT_CODE_NO_IP = 4;

    private const MICROSECONDS_PER_SECOND = 1000000;

    public function __construct(
        private InstanceRepository $instanceRepository,
        private FloatingIpManager $floatingIpManager,
        private ActionRepository $actionRepository,
        private FloatingIpRepository $floatingIpRepository,
        private ActionRunner $actionRunner,
        private int $assigmentTimeoutInSeconds,
        private int $assignmentRetryInSeconds,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $instance = $this->instanceRepository->findCurrent();
        if (null === $instance) {
            return self::EXIT_CODE_NO_CURRENT_INSTANCE;
        }

        $assignedIp = $this->floatingIpRepository->find();
        if (null === $assignedIp) {
            return self::EXIT_CODE_NO_IP;
        }

        if ($instance->hasIp($assignedIp->getIp())) {
            return Command::SUCCESS;
        }

        $actionEntity = $this->floatingIpManager->reAssign($instance, $assignedIp->getIp());

        $this->actionRunner->run(
            new ActionHandler(
                function (ActionEntity $actionEntity): bool {
                    return 'completed' === $actionEntity->status;
                },
                function () use ($actionEntity) {
                    return $this->actionRepository->update($actionEntity);
                },
            ),
            $this->assigmentTimeoutInSeconds * self::MICROSECONDS_PER_SECOND,
            $this->assignmentRetryInSeconds * self::MICROSECONDS_PER_SECOND
        );

        return Command::SUCCESS;
    }
}
