<?php

namespace App\Command;

use App\ActionHandler\ActionHandler;
use App\Model\AssignedIp;
use App\Model\CommandOutput\CommandOutput;
use App\Model\Instance;
use App\Services\ActionRunner;
use App\Services\CommandOutputHandler;
use App\Services\FloatingIpManager;
use App\Services\FloatingIpRepository;
use App\Services\InstanceRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: IpCreateCommand::NAME,
    description: 'Create a new floating IP for the current instance.',
)]
class IpCreateCommand extends Command
{
    public const NAME = 'app:ip:create';
    public const EXIT_CODE_NO_CURRENT_INSTANCE = 3;
    public const EXIT_CODE_HAS_IP = 4;

    private const MICROSECONDS_PER_SECOND = 1000000;

    public function __construct(
        private InstanceRepository $instanceRepository,
        private FloatingIpManager $floatingIpManager,
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
                CommandOutput::createError(
                    'no-instance',
                )
            );

            return self::EXIT_CODE_NO_CURRENT_INSTANCE;
        }

        $instanceId = $instance->getId();

        $assignedIp = $this->floatingIpRepository->find();
        if ($assignedIp instanceof AssignedIp) {
            $this->outputHandler->writeOutput(
                CommandOutput::createError(
                    'has-ip',
                    [
                        'ip' => $assignedIp->getIp(),
                    ]
                )
            );

            return self::EXIT_CODE_HAS_IP;
        }

        $assignedIp = $this->floatingIpManager->create($instance);
        $ip = $assignedIp->getIp();

        $this->actionRunner->run(
            new ActionHandler(
                function (Instance $instance) use ($ip) {
                    return $instance->hasIp($ip);
                },
                function () use ($instance) {
                    return $this->instanceRepository->find($instance->getId());
                },
            ),
            $this->assigmentTimeoutInSeconds * self::MICROSECONDS_PER_SECOND,
            $this->assignmentRetryInSeconds * self::MICROSECONDS_PER_SECOND
        );

        $this->outputHandler->writeOutput(
            CommandOutput::createSuccess(
                'created',
                [
                    'ip' => $ip,
                    'target-instance' => $instanceId,
                ]
            )
        );

        return Command::SUCCESS;
    }
}
