<?php

namespace App\Services;

use App\Model\Instance;
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
    public function getVersion(Instance $instance): string
    {
        $url = $instance->getUrl() . '/version';
        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->httpClient->sendRequest($request);

        return $response->getBody()->getContents();
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
