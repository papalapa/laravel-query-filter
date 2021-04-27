<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\FilterNormalizer;
use PHPUnit\Framework\TestCase;

class FilterNormalizerTest extends TestCase
{
    public function test(): void
    {
        $expected = [
            'and' => [
                ['username' => 'admin'],
                ['is not null' => 'role'],
                ['is null' => 'deleted_at'],
                [
                    'or' => [
                        ['role' => 'administrator'],
                        ['role' => 'manager'],
                    ],
                ],
            ],
        ];

        $arrayFilter = $expected;
        $jsonFilter = json_encode($arrayFilter, JSON_THROW_ON_ERROR);

        $normalizer = new FilterNormalizer();

        self::assertEquals($expected, $normalizer->normalize($arrayFilter));
        self::assertEquals($expected, $normalizer->normalize($jsonFilter));
    }
}
