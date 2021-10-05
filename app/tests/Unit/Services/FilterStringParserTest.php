<?php

namespace App\Tests\Unit\Services;

use App\Model\Filter;
use App\Services\FilterStringParser;
use PHPUnit\Framework\TestCase;

class FilterStringParserTest extends TestCase
{
    /**
     * @dataProvider parseDataProvider
     *
     * @param Filter[] $expectedFilters
     */
    public function testParse(string $filter, array $expectedFilters): void
    {
        $filterStringParser = new FilterStringParser();

        self::assertEquals(
            $expectedFilters,
            $filterStringParser->parse($filter)
        );
    }

    /**
     * @return array<mixed>
     */
    public function parseDataProvider(): array
    {
        return [
            'empty' => [
                'filter' => '',
                'expectedFilters' => [],
            ],
            'non-json' => [
                'filter' => 'content',
                'expectedFilters' => [],
            ],
            'single invalid filter, field missing' => [
                'filter' => json_encode([
                    [
                        'operator' => '=',
                        'value' => 12,
                    ],
                ]),
                'expectedFilters' => [],
            ],
            'single invalid filter, operator invalid' => [
                'filter' => json_encode([
                    [
                        'field' => 'length',
                        'operator' => '>',
                        'value' => 12,
                    ],
                ]),
                'expectedFilters' => [],
            ],
            'single invalid filter, value missing' => [
                'filter' => json_encode([
                    [
                        'field' => 'title',
                        'operator' => '=',
                    ],
                ]),
                'expectedFilters' => [],
            ],
            'single valid filter, operator =' => [
                'filter' => json_encode([
                    [
                        'field' => 'title',
                        'operator' => '=',
                        'value' => 'Expected Title',
                    ],
                ]),
                'expectedFilters' => [
                    new Filter('title', '=', 'Expected Title'),
                ],
            ],
            'single valid filter, operator !contains' => [
                'filter' => json_encode([
                    [
                        'field' => 'ips',
                        'operator' => '!contains',
                        'value' => '127.0.0.1',
                    ],
                ]),
                'expectedFilters' => [
                    new Filter('ips', '!contains', '127.0.0.1'),
                ],
            ],
            'single valid filter, operator missing, operator defaults to =' => [
                'filter' => json_encode([
                    [
                        'field' => 'title',
                        'value' => 'Expected Title',
                    ],
                ]),
                'expectedFilters' => [
                    new Filter('title', '=', 'Expected Title'),
                ],
            ],
            'multiple valid filters' => [
                'filter' => json_encode([
                    [
                        'field' => 'length',
                        'operator' => '=',
                        'value' => 0,
                    ],
                    [
                        'field' => 'ips',
                        'operator' => '!contains',
                        'value' => '127.0.0.1',
                    ],
                ]),
                'expectedFilters' => [
                    new Filter('length', '=', 0),
                    new Filter('ips', '!contains', '127.0.0.1'),
                ],
            ],
        ];
    }
}
