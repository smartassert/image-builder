<?php

namespace App\Services;

class CommandExceptionRenderer
{
    public function __construct(
        private string $projectDirectory,
    ) {
    }

    public function render(\Throwable $throwable): string
    {
        $relativePath = str_replace($this->projectDirectory, '', $throwable->getFile());
        $relativePath = ltrim($relativePath, '/');

        return sprintf(
            '%s: %s' . "\n\n" . '%s' . "\n" . 'line %s',
            $throwable::class,
            $throwable->getMessage(),
            $relativePath,
            $throwable->getLine()
        );
    }
}
