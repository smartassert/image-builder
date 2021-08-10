<?php

namespace App\Services;

use App\Model\CommandOutput\CommandOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandOutputHandler
{
    private OutputInterface $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function writeOutput(CommandOutput $commandOutput): void
    {
        $this->output->write((string) json_encode($commandOutput));
    }
}
