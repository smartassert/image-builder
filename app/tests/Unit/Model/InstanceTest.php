<?php

namespace App\Tests\Unit\Model;

use App\Model\Instance;
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
                'instance' => InstanceFactory::create([
                    'id' => 123,
                    'networks' => [
                        'v4' => [
                            [
                                'ip_address' => '127.0.0.2',
                            ],
                        ],
                    ],
                ]),
                'ip' => '127.0.0.1',
                'expectedHas' => false,
            ],
            'single IP, matching' => [
                'instance' => InstanceFactory::create([
                    'id' => 123,
                    'networks' => [
                        'v4' => [
                            [
                                'ip_address' => '127.0.0.1',
                            ],
                        ],
                    ],
                ]),
                'ip' => '127.0.0.1',
                'expectedHas' => true,
            ],
            'three IPs, third matching' => [
                'instance' => InstanceFactory::create([
                    'id' => 123,
                    'networks' => [
                        'v4' => [
                            [
                                'ip_address' => '127.0.0.1',
                            ],
                            [
                                'ip_address' => '127.0.0.2',
                            ],
                            [
                                'ip_address' => '127.0.0.3',
                            ],
                        ],
                    ],
                ]),
                'ip' => '127.0.0.3',
                'expectedHas' => true,
            ],
        ];
    }
}
