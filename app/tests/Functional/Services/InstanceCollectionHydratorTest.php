<?php

namespace App\Tests\Functional\Services;

use App\Model\Instance;
use App\Model\InstanceCollection;
use App\Services\InstanceCollectionHydrator;
use App\Tests\Services\HttpResponseFactory;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstanceCollectionHydratorTest extends KernelTestCase
{
    private InstanceCollectionHydrator $instanceCollectionHydrator;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $instanceCollectionHydrator = self::getContainer()->get(InstanceCollectionHydrator::class);
        \assert($instanceCollectionHydrator instanceof InstanceCollectionHydrator);
        $this->instanceCollectionHydrator = $instanceCollectionHydrator;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    public function testHydrateVersions(): void
    {
        $instanceCollectionData = [
            123 => [
                'ipAddress' => '127.0.0.1',
                'version' => '0.1',
            ],
            456 => [
                'ipAddress' => '127.0.0.2',
                'version' => '0.2',
            ],
        ];

        $instances = [];
        foreach ($instanceCollectionData as $dropletId => $instanceData) {
            $instances[] = new Instance($this->createDroplet($dropletId, $instanceData['ipAddress']));
            $this->mockHandler->append($this->httpResponseFactory->createFromArray([
                HttpResponseFactory::KEY_STATUS_CODE => 200,
                HttpResponseFactory::KEY_BODY => $instanceData['version'],
            ]));
        }

        $instanceCollection = new InstanceCollection($instances);
        $hydratedCollection = $this->instanceCollectionHydrator->hydrateVersions($instanceCollection);

        foreach ($hydratedCollection as $hydratedInstance) {
            $expectedData = $instanceCollectionData[$hydratedInstance->getId()];
            $expectedVersion = $expectedData['version'];
            self::assertSame($expectedVersion, $hydratedInstance->getVersion());
        }
    }

    private function createDroplet(int $id, string $ipAddress): DropletEntity
    {
        return new DropletEntity([
            'id' => $id,
            'networks' => [
                'v4' => [
                    [
                        'type' => 'public',
                        'ip_address' => $ipAddress,
                    ],
                ],
            ],
        ]);
    }
}