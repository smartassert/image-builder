<?php

namespace App\Tests\Services;

use App\Model\Image;
use DigitalOceanV2\Entity\Snapshot;

class ImageFactory
{
    /**
     * @param array<mixed> $snapshotData
     */
    public static function create(array $snapshotData): Image
    {
        return new Image(
            new Snapshot($snapshotData)
        );
    }
}
