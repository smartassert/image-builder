<?php

namespace App\Tests\Services;

use App\Model\Instance;
use DigitalOceanV2\Entity\Droplet;

class InstanceFactory
{
    /**
     * @param array<mixed> $dropletData
     */
    public static function create(array $dropletData, ?string $version = null): Instance
    {
        $instance = new Instance(
            new Droplet(self::normalizeDropletData($dropletData))
        );

        if (is_string($version)) {
            $instance = $instance->withVersion($version);
        }

        return $instance;
    }

    /**
     * @param array<mixed> $dropletData
     *
     * @return array<mixed>
     */
    private static function normalizeDropletData(array $dropletData): array
    {
        if (array_key_exists('networks', $dropletData)) {
            $networksData = $dropletData['networks'];
            if (is_array($networksData)) {
                if (array_key_exists('v4', $networksData)) {
                    $networksData['v4'] = self::normalizeNetworksCollectionData($networksData['v4']);
                }

                if (array_key_exists('v6', $networksData)) {
                    $networksData['v6'] = self::normalizeNetworksCollectionData($networksData['v6']);
                }

                $networksData = (object) $networksData;
            }

            $dropletData['networks'] = $networksData;
        }

        return $dropletData;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function normalizeNetworksCollectionData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = (object) $value;
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
