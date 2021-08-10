<?php

namespace App\Services;

use Symfony\Component\Console\Output\OutputInterface;

class CommandOutputHandler
{
    private OutputInterface $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @param array<mixed> $data
     */
    public function createSuccessOutput(array $data = []): void
    {
        $this->createOutput('success', $data);
    }

    /**
     * @param array<mixed> $data
     */
    public function createErrorOutput(string $errorCode, array $data = []): void
    {
        $this->createOutput('error', array_merge(
            ['error-code' => $errorCode],
            $data
        ));
    }

    /**
     * @param 'success'|'error' $status
     * @param array<mixed>      $data
     */
    private function createOutput(string $status, array $data = []): void
    {
        $this->output->write((string) json_encode(array_merge(
            [
                'status' => $status,
            ],
            $data
        )));
    }
}
