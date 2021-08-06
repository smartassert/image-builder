<?php

namespace App\Model;

class DropletAction implements DropletActionInterface
{
    /**
     * @param \Closure(int $id): mixed $action
     */
    public function __construct(
        private \Closure $action,
    ) {
    }

    public function __invoke(int $id): mixed
    {
        return ($this->action)($id);
    }
}
