<?php

namespace App\Services;

use App\Model\Image;
use DigitalOceanV2\Api\Snapshot as SnapshotApi;
use DigitalOceanV2\Exception\ExceptionInterface;
use DigitalOceanV2\Exception\RuntimeException;

class ImageRepository
{
    public function __construct(
        private SnapshotApi $snapshotApi,
        private string $imageName,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function find(): ?Image
    {
        try {
            return new Image(
                $this->snapshotApi->getById($this->imageName)
            );
        } catch (ExceptionInterface $exception) {
            if ($exception instanceof RuntimeException && 404 === $exception->getCode()) {
                return null;
            }

            throw $exception;
        }
    }
}
