<?php

namespace App\Model;

use DigitalOceanV2\Entity\Droplet;

class Instance implements \JsonSerializable
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
        return array_merge(
            $this->state,
            [
                'ips' => $this->getIps(),
            ]
        );
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
    public function withAdditionalState(array $state): self
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

    /**
     * @return string[]
     */
    public function getIps(): array
    {
        $ips = [];

        foreach ($this->droplet->networks as $network) {
            $ips[] = $network->ipAddress;
        }

        return array_unique($ips);
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

    public function isMatchedBy(Filter $filter): bool
    {
        $state = $this->getState();
        $operator = $filter->getOperator();

        if (Filter::OPERATOR_EQUALS === $operator) {
            return $filter->getValue() === ($state[$filter->getField()] ?? null);
        }

        if (Filter::OPERATOR_NOT_CONTAINS === $operator) {
            $haystack = ($state[$filter->getField()] ?? []);
            $haystack = is_array($haystack) ? $haystack : [];

            return !in_array($filter->getValue(), $haystack);
        }

        return false;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'state' => $this->getState(),
        ];
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
