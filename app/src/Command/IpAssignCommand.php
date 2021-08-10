<?php

namespace App\Command;

use App\ActionHandler\ActionHandler;
use App\Exception\ActionTimeoutException;
use App\Model\CommandOutput\CommandOutput;
use App\Services\ActionRepository;
use App\Services\ActionRunner;
use App\Services\CommandOutputHandler;
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
    public const EXIT_CODE_ASSIGNMENT_TIMED_OUT = 5;

    private const MICROSECONDS_PER_SECOND = 1000000;

    public function __construct(
        private InstanceRepository $instanceRepository,
        private FloatingIpManager $floatingIpManager,
        private ActionRepository $actionRepository,
        private FloatingIpRepository $floatingIpRepository,
        private ActionRunner $actionRunner,
        private CommandOutputHandler $outputHandler,
        private int $assigmentTimeoutInSeconds,
        private int $assignmentRetryInSeconds,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHandler->setOutput($output);

        $instance = $this->instanceRepository->findCurrent();
        if (null === $instance) {
            $this->outputHandler->writeOutput(
                CommandOutput::createError('no-instance')
            );

            return self::EXIT_CODE_NO_CURRENT_INSTANCE;
        }

        $assignedIp = $this->floatingIpRepository->find();
        if (null === $assignedIp) {
            $this->outputHandler->writeOutput(
                CommandOutput::createError('no-ip')
            );

            return self::EXIT_CODE_NO_IP;
        }

        $ip = $assignedIp->getIp();
        $sourceInstanceId = $assignedIp->getInstance()->getId();
        $targetInstanceId = $instance->getId();

        if ($instance->hasIp($ip)) {
            $this->outputHandler->writeOutput(
                CommandOutput::createSuccess(
                    'already-assigned',
                    [
                        'ip' => $ip,
                        'source-instance' => $targetInstanceId,
                        'target-instance' => $targetInstanceId,
                    ]
                )
            );

            return Command::SUCCESS;
        }

        $actionEntity = $this->floatingIpManager->reAssign($instance, $ip);

        try {
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

            $this->outputHandler->writeOutput(
                CommandOutput::createSuccess(
                    're-assigned',
                    [
                        'ip' => $ip,
                        'source-instance' => $sourceInstanceId,
                        'target-instance' => $targetInstanceId,
                    ]
                )
            );

            return Command::SUCCESS;
        } catch (ActionTimeoutException) {
            $this->outputHandler->writeOutput(
                CommandOutput::createError(
                    'assignment-timed-out',
                    [
                        'ip' => $ip,
                        'source-instance' => $sourceInstanceId,
                        'target-instance' => $targetInstanceId,
                        'timeout-in-seconds' => $this->assigmentTimeoutInSeconds,
                    ]
                )
            );

            return self::EXIT_CODE_ASSIGNMENT_TIMED_OUT;
        }
    }
}
