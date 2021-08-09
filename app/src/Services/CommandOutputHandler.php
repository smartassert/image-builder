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
        $this->writeOutput(false, $commandOutput);
    }

    public function writeSuccess(CommandOutput $commandOutput): void
    {
        $this->writeOutput(true, $commandOutput);
    }

    public function writeOutput(bool $isSuccessful, CommandOutput $commandOutput): void
    {
        $this->output->write($this->createJsonOutput(
            $isSuccessful ? 'success' : 'error',
            $commandOutput
        ));
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
