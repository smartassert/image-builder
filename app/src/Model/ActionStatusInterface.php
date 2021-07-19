<?php

namespace App\Model;

interface ActionStatusInterface
{
    public const STATUS_IN_PROGRESS = 'in-progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ERRORED = 'errored';
    public const STATUS_UNKNOWN = 'unknown';
}
