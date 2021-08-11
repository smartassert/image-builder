<?php

namespace App\Services;

use Symfony\Component\Console\Output\OutputInterface;

class CommandOutputHandler
{
    /**
     * @param array<mixed> $data
     */
    public function createSuccessOutput(OutputInterface $output, array $data = []): void
    {
        $this->createOutput($output, 'success', $data);
    }

    /**
     * @param array<mixed> $data
     */
    public function createErrorOutput(OutputInterface $output, string $errorCode, array $data = []): void
    {
        $this->createOutput($output, 'error', array_merge(
            ['error-code' => $errorCode],
            $data
        ));
    }

    /**
     * @param 'success'|'error' $status
     * @param array<mixed>      $data
     */
    private function createOutput(OutputInterface $output, string $status, array $data = []): void
    {
        $output->write((string) json_encode(array_merge(
            [
                'status' => $status,
            ],
            $data
        )));
    }
}
