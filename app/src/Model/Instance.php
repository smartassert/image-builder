<?php

namespace App\Model;

use DigitalOceanV2\Entity\Droplet;

class Instance
{
    private ?string $version = null;

    public function __construct(private Droplet $droplet)
    {
    }

    public function getId(): int
    {
        return $this->droplet->id;
    }

    public function getDroplet(): Droplet
    {
        return $this->droplet;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function withVersion(string $version): self
    {
        $new = clone $this;
        $new->version = $version;

        return $new;
    }

    public function getUrl(): ?string
    {
        $ip = $this->getFirstPublicV4IpAddress();
        if (null === $ip) {
            return null;
        }

        return sprintf('http://%s', $ip);
    }

    private function getFirstPublicV4IpAddress(): ?string
    {
        foreach ($this->droplet->networks as $network) {
            if (4 === $network->version && 'public' === $network->type) {
                return $network->ipAddress;
            }
        }

        return null;
    }
}
