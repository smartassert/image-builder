<?php

namespace App\Tests\Functional\Services;

use App\Decider\Decider;
use App\Exception\ActionTimeoutException;
use App\Model\Instance;
use App\Services\ActionRunner;
use App\Services\FloatingIpManager;
use App\Services\InstanceRepository;
use App\Tests\Services\HttpResponseFactory;
use GuzzleHttp\Handler\MockHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ActionRunnerTest extends KernelTestCase
{
    private const MICROSECONDS_PER_SECOND = 1000000;

    private ActionRunner $fooService;

    protected function setUp(): void
    {
        parent::setUp();

        $fooService = self::getContainer()->get(ActionRunner::class);
        \assert($fooService instanceof ActionRunner);
        $this->fooService = $fooService;
    }

    /**
     * @dataProvider runSuccessSimpleDataProvider
     */
    public function testRunSuccessSimple(
        Decider $decider,
        int $maximumDurationInMicroseconds,
        int $retryPeriodInMicroseconds
    ): void {
        $this->fooService->run($decider, $maximumDurationInMicroseconds, $retryPeriodInMicroseconds);
        self::expectNotToPerformAssertions();
    }

    /**
     * @return array<mixed>
     */
    public function runSuccessSimpleDataProvider(): array
    {
        $delayedSuccessCount = 0;
        $delayedSuccessLimit = 3;

        return [
            'immediate success' => [
                'decider' => new Decider(
                    function () {
                        return true;
                    },
                    function () {
                    }
                ),
                'maximumDurationInMicroSeconds' => 1000,
                'retryPeriodInMicroseconds' => 10,
            ],
            'delayed success, basic' => [
                'decider' => new Decider(
                    function () use ($delayedSuccessLimit, &$delayedSuccessCount) {
                        if ($delayedSuccessCount < $delayedSuccessLimit) {
                            ++$delayedSuccessCount;

                            return false;
                        }

                        return true;
                    },
                    function () {
                    }
                ),
                'maximumDurationInMicroSeconds' => 1000,
                'retryPeriodInMicroseconds' => 10,
            ],
        ];
    }

    public function testRunSuccessComplex(): void
    {
        $mockHandler = self::getContainer()->get(MockHandler::class);
        \assert($mockHandler instanceof MockHandler);

        $httpResponseFactory = self::getContainer()->get(HttpResponseFactory::class);
        \assert($httpResponseFactory instanceof HttpResponseFactory);

        $expectedIp = '127.0.0.2';

        $mockHandler->append(...[
            'find current instance' => $httpResponseFactory->createFromArray([
                HttpResponseFactory::KEY_STATUS_CODE => 200,
                HttpResponseFactory::KEY_HEADERS => [
                    'content-type' => 'application/json; charset=utf-8',
                ],
                HttpResponseFactory::KEY_BODY => (string) json_encode([
                    'droplets' => [
                        [
                            'id' => 123,
                        ],
                    ],
                ])
            ]),
            'get instance, does not have expected IP' => $httpResponseFactory->createFromArray([
                HttpResponseFactory::KEY_STATUS_CODE => 200,
                HttpResponseFactory::KEY_HEADERS => [
                    'content-type' => 'application/json; charset=utf-8',
                ],
                HttpResponseFactory::KEY_BODY => (string) json_encode([
                    'droplet' => [
                        'id' => 123,
                        'networks' => [
                            'v4' => [
                                [
                                    'ip_address' => '127.0.0.1',
                                ],
                            ],
                        ],
                    ],
                ])
            ]),
            'get instance, has expected IP' => $httpResponseFactory->createFromArray([
                HttpResponseFactory::KEY_STATUS_CODE => 200,
                HttpResponseFactory::KEY_HEADERS => [
                    'content-type' => 'application/json; charset=utf-8',
                ],
                HttpResponseFactory::KEY_BODY => (string) json_encode([
                    'droplet' => [
                        'id' => 123,
                        'networks' => [
                            'v4' => [
                                [
                                    'ip_address' => '127.0.0.1',
                                ],
                                [
                                    'ip_address' => $expectedIp,
                                ],
                            ],
                        ],
                    ],
                ])
            ]),
        ]);

        $floatingIpManager = self::getContainer()->get(FloatingIpManager::class);
        \assert($floatingIpManager instanceof FloatingIpManager);

        $instanceRepository = self::getContainer()->get(InstanceRepository::class);
        \assert($instanceRepository instanceof InstanceRepository);

        $instance = $instanceRepository->findCurrent();
        \assert($instance instanceof Instance);

        $decider = new Decider(
            function (Instance $instance) use ($expectedIp) {
                return $instance->hasIp($expectedIp);
            },
            function () use ($instance, $instanceRepository) {
                return $instanceRepository->find($instance->getId());
            }
        );

        $maximumDurationInMicroseconds = (int) (self::MICROSECONDS_PER_SECOND * 10);
        $retryPeriodInMicroseconds = (int) (self::MICROSECONDS_PER_SECOND * 0.1);

        $this->fooService->run($decider, $maximumDurationInMicroseconds, $retryPeriodInMicroseconds);

        self::assertCount(0, $mockHandler);
    }

    public function testRunFailure(): void
    {
        $decider = new Decider(
            function () {
                return false;
            },
            function () {
            }
        );

        self::expectException(ActionTimeoutException::class);

        $this->fooService->run($decider, 10, 1);
    }
}
