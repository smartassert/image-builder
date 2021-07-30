<?php

namespace App\Command;

use App\ActionHandler\ActionHandler;
use App\Exception\ActionTimeoutException;
use App\Model\CommandOutput\CommandOutput;
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
    public const EXIT_CODE_ASSIGNMENT_TIMED_OUT = 5;

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
            $output->write(
                $this->createErrorOutput(new CommandOutput(
                    'no-instance',
                    'Cannot re-assign IP, no current instance found'
                ))
            );

            return self::EXIT_CODE_NO_CURRENT_INSTANCE;
        }

        $assignedIp = $this->floatingIpRepository->find();
        if (null === $assignedIp) {
            $output->write(
                $this->createErrorOutput(new CommandOutput(
                    'no-ip',
                    'Cannot re-assign IP, none found'
                ))
            );

            return self::EXIT_CODE_NO_IP;
        }

        $ip = $assignedIp->getIp();
        $sourceInstanceId = $assignedIp->getInstance()->getId();
        $targetInstanceId = $instance->getId();

        if ($instance->hasIp($ip)) {
            $output->write(
                $this->createSuccessOutput(new CommandOutput(
                    'already-assigned',
                    sprintf(
                        '%s is already assigned to instance %s',
                        $ip,
                        $targetInstanceId
                    ),
                    [
                        'ip' => $ip,
                        'source-instance' => $targetInstanceId,
                        'target-instance' => $targetInstanceId,
                    ]
                ))
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

            $output->write(
                $this->createSuccessOutput(new CommandOutput(
                    're-assigned',
                    sprintf(
                        'Re-assigned %s from instance %s to instance %s',
                        $ip,
                        $sourceInstanceId,
                        $targetInstanceId
                    ),
                    [
                        'ip' => $ip,
                        'source-instance' => $sourceInstanceId,
                        'target-instance' => $targetInstanceId,
                    ]
                ))
            );

            return Command::SUCCESS;
        } catch (ActionTimeoutException) {
            $output->write(
                $this->createErrorOutput(new CommandOutput(
                    'assignment-timed-out',
                    sprintf(
                        'Waited %d seconds to assign %s from instance %s to instance %s',
                        $this->assigmentTimeoutInSeconds,
                        $ip,
                        $sourceInstanceId,
                        $targetInstanceId
                    ),
                    [
                        'ip' => $ip,
                        'source-instance' => $sourceInstanceId,
                        'target-instance' => $targetInstanceId,
                        'timeout-in-seconds' => $this->assigmentTimeoutInSeconds,
                    ]
                ))
            );

            return self::EXIT_CODE_ASSIGNMENT_TIMED_OUT;
        }
    }

    private function createSuccessOutput(CommandOutput $commandOutput): string
    {
        return $this->createJsonOutput('success', $commandOutput);
    }

    private function createErrorOutput(CommandOutput $commandOutput): string
    {
        return $this->createJsonOutput('error', $commandOutput);
    }

    /**
     * @param "success"|"error" $type
     */
    private function createJsonOutput(string $type, CommandOutput $commandOutput): string
    {
        return (string) json_encode([
            $type => $commandOutput,
        ]);
    }
}
