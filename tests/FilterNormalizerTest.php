<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\FilterNormalizer;
use PHPUnit\Framework\TestCase;

class FilterNormalizerTest extends TestCase
{
    public function test(): void
    {
        $expected = [
            'and'   => [
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
            'or'    => [
                ['created_at' => '>=1970-01-01 00:00:00'],
                ['updated_at' => '>=1970-01-01 00:00:00'],
            ],
            'email' => '*@gmail.com',
        ];

        $arrayFilter = $expected;
        $jsonFilter  = json_encode($arrayFilter, JSON_THROW_ON_ERROR);

        $normalizer = new FilterNormalizer();

        self::assertEquals($expected, $normalizer->normalize($arrayFilter));
        self::assertEquals($expected, $normalizer->normalize($jsonFilter));
    }
}
