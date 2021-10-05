<?php

namespace App\Model;

use DigitalOceanV2\Entity\Droplet;

class Instance
{
    private ?string $version = null;
    private ?int $messageQueueSize = null;

    /**
     * @var array<int|string, mixed>
     */
    private array $state = [];

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

    /**
     * @return array<int|string, mixed>
     */
    public function getState(): array
    {
        return $this->state;
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

    /**
     * @param array<int|string, mixed> $state
     */
    public function withState(array $state): self
    {
        $new = clone $this;
        $new->state = $state;

        return $new;
    }

    public function getMessageQueueSize(): ?int
    {
        return $this->messageQueueSize;
    }

    public function withMessageQueueSize(int $messageQueueSize): self
    {
        $new = clone $this;
        $new->messageQueueSize = $messageQueueSize;

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

    public function hasIp(string $ip): bool
    {
        foreach ($this->droplet->networks as $network) {
            if ($ip === $network->ipAddress) {
                return true;
            }
        }

        return false;
    }

    public function getLabel(): string
    {
        $tagsComponent = implode(', ', $this->droplet->tags);
        if ('' === $tagsComponent) {
            $tagsComponent = '[no tags]';
        }

        return sprintf(
            '%s (%s)',
            $this->getId(),
            $tagsComponent,
        );
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->droplet->createdAt);
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
