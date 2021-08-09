<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class Application extends ConsoleApplication
{
    public function renderThrowable(\Throwable $e, OutputInterface $output): void
    {
        $projectDirectory = $this->getKernel()->getProjectDir();
        $relativePath = ltrim(
            str_replace($projectDirectory, '', $e->getFile()),
            '/'
        );

        if ($output instanceof StreamOutput) {
            $output = new ConsoleOutput();
        }

        $output->write((string) json_encode([
            'exception' => [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'relative-path' => $relativePath,
                'line' => $e->getLine(),
            ],
        ]));
    }
}
