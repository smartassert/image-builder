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

    public function writeError(CommandOutput $commandOutput): void
    {
        $this->output->write(
            $this->createErrorOutput($commandOutput)
        );
    }

    public function writeSuccess(CommandOutput $commandOutput): void
    {
        $this->output->write(
            $this->createSuccessOutput($commandOutput)
        );
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
