<?php

namespace App\Decider;

class Decider
{
    /**
     * @param \Closure(mixed $actionResult): bool $decider
     */
    public function __construct(
        private \Closure $decider
    ) {
    }

    public function __invoke(mixed $actionResult): bool
    {
        return ($this->decider)($actionResult);
    }
}
