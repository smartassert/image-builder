<?php

namespace App\Services;

use App\Model\Instance;
use App\Model\InstanceHealth;
use App\Model\InstanceStatus;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class InstanceClient
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getStatus(Instance $instance): ?InstanceStatus
    {
        $url = $instance->getUrl() . '/';
        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->httpClient->sendRequest($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (is_array($responseData)) {
            return $this->createInstanceStatus($responseData);
        }

        return null;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getHealth(Instance $instance): ?InstanceHealth
    {
        $url = $instance->getUrl() . '/health-check';
        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->httpClient->sendRequest($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (is_array($responseData)) {
            return new InstanceHealth($responseData);
        }

        return null;
    }

    /**
     * @param array<mixed> $data
     */
    private function createInstanceStatus(array $data): ?InstanceStatus
    {
        $version = $data['version'] ?? null;
        $version = is_string($version) ? $version : null;

        $messageQueueSize = $data['message-queue-size'] ?? null;
        $messageQueueSize = is_int($messageQueueSize) ? $messageQueueSize : null;

        return is_string($version) && is_int($messageQueueSize)
            ? new InstanceStatus($version, $messageQueueSize)
            : null;
    }
}
