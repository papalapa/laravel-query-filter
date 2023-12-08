<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\AttributeMapper;
use Papalapa\Laravel\QueryFilter\Sorting;
use PHPUnit\Framework\TestCase;

class SortingTest extends TestCase
{
    public function test(): void
    {
        $sorting = new Sorting(new AttributeMapper());
        $sorting
            ->useMap([
                'id'        => 'id',
                'full_name' => ['firstname', 'lastname'],
                'datetime'  => 'created_at',
            ])
            ->setDefaultSorting([
                'created_at' => 'desc',
            ])
            ->setFinalSorting([
                'serial' => 'desc',
            ]);

        $sorting->sort(requestedSorting: 'full_name,-id');
        self::assertEquals('desc', $sorting->getDirection('id'));
        self::assertEquals('asc', $sorting->getDirection('firstname'));
        self::assertEquals('asc', $sorting->getDirection('lastname'));
        self::assertEquals('desc', $sorting->getDirection('serial'));

        $sorting->sort(requestedSorting: 'datetime', requestedOrdering: 'desc');
        self::assertEquals('desc', $sorting->getDirection('created_at'));
        self::assertEquals('desc', $sorting->getDirection('serial'));

        $sorting->sort();
        self::assertEquals('desc', $sorting->getDirection('created_at'));
    }
}
