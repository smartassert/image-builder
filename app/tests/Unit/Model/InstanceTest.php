<?php

namespace App\Tests\Unit\Model;

use App\Model\Filter;
use App\Model\Instance;
use App\Tests\Services\DropletDataFactory;
use App\Tests\Services\InstanceFactory;
use PHPUnit\Framework\TestCase;

class InstanceTest extends TestCase
{
    /**
     * @dataProvider hasIpDataProvider
     */
    public function testHasIp(Instance $instance, string $ip, bool $expectedHas): void
    {
        self::assertSame($expectedHas, $instance->hasIp($ip));
    }

    /**
     * @return array<mixed>
     */
    public function hasIpDataProvider(): array
    {
        return [
            'no IPs' => [
                'instance' => InstanceFactory::create([
                    'id' => 123,
                ]),
                'ip' => '127.0.0.1',
                'expectedHas' => false,
            ],
            'no matching IP' => [
                'instance' => InstanceFactory::create(
                    DropletDataFactory::createWithIps(123, ['127.0.0.2'])
                ),
                'ip' => '127.0.0.1',
                'expectedHas' => false,
            ],
            'single IP, matching' => [
                'instance' => InstanceFactory::create(
                    DropletDataFactory::createWithIps(123, ['127.0.0.1'])
                ),
                'ip' => '127.0.0.1',
                'expectedHas' => true,
            ],
            'three IPs, third matching' => [
                'instance' => InstanceFactory::create(
                    DropletDataFactory::createWithIps(123, ['127.0.0.1', '127.0.0.2', '127.0.0.3'])
                ),
                'ip' => '127.0.0.3',
                'expectedHas' => true,
            ],
        ];
    }

    /**
     * @dataProvider getLabelDataProvider
     */
    public function testGetLabel(Instance $instance, string $expectedLabel): void
    {
        self::assertSame($expectedLabel, $instance->getLabel());
    }

    /**
     * @return array<mixed>
     */
    public function getLabelDataProvider(): array
    {
        return [
            'no tags' => [
                'instance' => InstanceFactory::create([
                    'id' => 123,
                ]),
                'expectedLabel' => '123 ([no tags])',
            ],
            'single tag' => [
                'instance' => InstanceFactory::create([
                    'id' => 456,
                    'tags' => [
                        'tag1',
                    ],
                ]),
                'expectedLabel' => '456 (tag1)',
            ],
            'multiple tags' => [
                'instance' => InstanceFactory::create([
                    'id' => 789,
                    'tags' => [
                        'tag1',
                        'tag2',
                        'tag3',
                    ],
                ]),
                'expectedLabel' => '789 (tag1, tag2, tag3)',
            ],
        ];
    }

    /**
     * @dataProvider isMatchedByDataProvider
     */
    public function testIsMatchedBy(Instance $instance, Filter $filter, bool $expected): void
    {
        self::assertSame($expected, $instance->isMatchedBy($filter));
    }

    /**
     * @return array<mixed>
     */
    public function isMatchedByDataProvider(): array
    {
        $messageQueueSizeFilter = new Filter('message-queue-size', Filter::OPERATOR_EQUALS, 0);
        $notContainsIpFilter = new Filter('ips', Filter::OPERATOR_NOT_CONTAINS, '127.0.0.1');

        return [
            'equals, instance does not have state property' => [
                'instance' => InstanceFactory::create([
                    'id' => 1,
                ]),
                'filter' => $messageQueueSizeFilter,
                'expected' => false,
            ],
            'equals, instance has state property, property does not match' => [
                'instance' => InstanceFactory::create([
                    'id' => 2,
                ])->withAdditionalState([
                    'message-queue-size' => 12,
                ]),
                'filter' => $messageQueueSizeFilter,
                'expected' => false,
            ],
            'equals, instance has state property, property matches' => [
                'instance' => InstanceFactory::create([
                    'id' => 3,
                ])->withAdditionalState([
                    'message-queue-size' => 0,
                ]),
                'filter' => $messageQueueSizeFilter,
                'expected' => true,
            ],
            'contains, instance does not have state property' => [
                'instance' => InstanceFactory::create([
                    'id' => 4,
                ]),
                'filter' => $notContainsIpFilter,
                'expected' => true,
            ],
            'contains, instance has state property, property does not match' => [
                'instance' => InstanceFactory::create(DropletDataFactory::createWithIps(
                    5,
                    [
                        '127.0.0.2',
                        '127.0.0.3',
                    ]
                )),
                'filter' => $notContainsIpFilter,
                'expected' => true,
            ],
            'contains, instance has state property, property matches' => [
                'instance' => InstanceFactory::create(DropletDataFactory::createWithIps(
                    6,
                    [
                        '127.0.0.1',
                        '127.0.0.2',
                        '127.0.0.3',
                    ]
                )),
                'filter' => $notContainsIpFilter,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider jsonSerializeDataProvider
     *
     * @param array<mixed> $expected
     */
    public function testJsonSerialize(Instance $instance, array $expected): void
    {
        self::assertSame($expected, $instance->jsonSerialize());
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerializeDataProvider(): array
    {
        return [
            'id-only' => [
                'instance' => InstanceFactory::create([
                    'id' => 123,
                ]),
                'expected' => [
                    'id' => 123,
                    'state' => [
                        'ips' => [],
                    ],
                ],
            ],
            'id and IP addresses' => [
                'instance' => InstanceFactory::create(DropletDataFactory::createWithIps(
                    456,
                    [
                        '127.0.0.1',
                        '10.0.0.1',
                    ]
                )),
                'expected' => [
                    'id' => 456,
                    'state' => [
                        'ips' => [
                            '127.0.0.1',
                            '10.0.0.1',
                        ],
                    ],
                ],
            ],
            'id, no IP addresses, additional custom state' => [
                'instance' => InstanceFactory::create([
                    'id' => 789
                ])->withAdditionalState([
                    'key1' => 'value1',
                    'key2' => 'value2',
                ]),
                'expected' => [
                    'id' => 789,
                    'state' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'ips' => [],
                    ],
                ],
            ],
            'id, IP addresses, additional custom state' => [
                'instance' => InstanceFactory::create(DropletDataFactory::createWithIps(
                    321,
                    [
                        '127.0.0.2',
                        '10.0.0.2',
                    ]
                ))->withAdditionalState([
                    'key1' => 'value1',
                    'key2' => 'value2',
                ]),
                'expected' => [
                    'id' => 321,
                    'state' => [
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'ips' => [
                            '127.0.0.2',
                            '10.0.0.2',
                        ],
                    ],
                ],
            ],
        ];
    }
}
