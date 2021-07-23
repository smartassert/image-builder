<?php

namespace App\Tests\Functional\Services;

use App\Model\Image;
use App\Services\ImageRepository;
use App\Tests\Services\HttpResponseFactory;
use App\Tests\Services\ImageFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageRepositoryTest extends KernelTestCase
{
    private ImageRepository $imageRepository;
    private MockHandler $mockHandler;
    private HttpResponseFactory $httpResponseFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $imageRepository = self::getContainer()->get(ImageRepository::class);
        \assert($imageRepository instanceof ImageRepository);
        $this->imageRepository = $imageRepository;

        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);
        $this->mockHandler = $mockHandler;

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);
        $this->httpResponseFactory = $httpResponseFactory;
    }

    /**
     * @dataProvider findDataProvider
     *
     * @param array<mixed> $httpResponseData
     */
    public function testFind(array $httpResponseData, ?Image $expectedImage): void
    {
        $this->mockHandler->append(
            $this->httpResponseFactory->createFromArray($httpResponseData)
        );

        $instance = $this->imageRepository->find();
        self::assertEquals($expectedImage, $instance);
    }

    /**
     * @return array<mixed>
     */
    public function findDataProvider(): array
    {
        return [
            'not found' => [
                'httpResponseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 404,
                ],
                'expectedImage' => null,
            ],
            'found' => [
                'httpResponseData' => [
                    HttpResponseFactory::KEY_STATUS_CODE => 200,
                    HttpResponseFactory::KEY_HEADERS => [
                        'content-type' => 'application/json; charset=utf-8',
                    ],
                    HttpResponseFactory::KEY_BODY => (string) json_encode([
                        'snapshot' => [
                            'id' => 123,
                        ],
                    ]),
                ],
                'expectedImage' => ImageFactory::create(['id' => 123]),
            ],
        ];
    }
}
