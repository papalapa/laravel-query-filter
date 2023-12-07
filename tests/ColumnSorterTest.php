<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\AttributeMapper;
use Papalapa\Laravel\QueryFilter\ColumnSorter;
use PHPUnit\Framework\TestCase;

class ColumnSorterTest extends TestCase
{
    public function test(): void
    {
        $sorter = new ColumnSorter(new AttributeMapper());
        $sorter
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

        $sorter->sort(requestedSorting: 'full_name,-id');
        self::assertEquals('desc', $sorter->getDirection('id'));
        self::assertEquals('asc', $sorter->getDirection('firstname'));
        self::assertEquals('asc', $sorter->getDirection('lastname'));
        self::assertEquals('desc', $sorter->getDirection('serial'));

        $sorter->sort(requestedSorting: 'datetime', requestedOrdering: 'desc');
        self::assertEquals('desc', $sorter->getDirection('created_at'));
        self::assertEquals('desc', $sorter->getDirection('serial'));

        $sorter->sort();
        self::assertEquals('desc', $sorter->getDirection('created_at'));
    }
}
