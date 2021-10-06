<?php

namespace App\Services;

use App\Model\Instance;
use App\Model\InstanceHealth;
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
     *
     * @return array<int|string, mixed>
     */
    public function getState(Instance $instance): array
    {
        $url = $instance->getUrl() . '/';
        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->httpClient->sendRequest($request);
        if ('application/json' !== $response->getHeaderLine('content-type')) {
            return [];
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        return is_array($responseData) ? $responseData : [];
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getHealth(Instance $instance): InstanceHealth
    {
        $url = $instance->getUrl() . '/health-check';
        $request = $this->requestFactory->createRequest('GET', $url);

        $response = $this->httpClient->sendRequest($request);
        $responseData = json_decode($response->getBody()->getContents(), true);

        return new InstanceHealth(is_array($responseData) ? $responseData : []);
    }
}
