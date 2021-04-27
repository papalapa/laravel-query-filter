<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\ColumnSorter;
use PHPUnit\Framework\TestCase;

final class ColumnSorterTest extends TestCase
{
    public function test(): void
    {
        $s = new ColumnSorter(attributesMap: [
            'id' => 'id',
            'full_name' => ['firstname', 'lastname'],
            'datetime' => 'created_at',
        ], defaultSorting: [
            'created_at' => 'desc',
        ], finalSorting: [
            'serial' => 'desc',
        ]);

        $s->sort('full_name,-id');
        self::assertEquals('desc', $s->getDirection('id'));
        self::assertEquals('asc', $s->getDirection('firstname'));
        self::assertEquals('asc', $s->getDirection('lastname'));
        self::assertEquals('desc', $s->getDirection('serial'));

        $s->sort('datetime', 'desc');
        self::assertEquals('desc', $s->getDirection('created_at'));
        self::assertEquals('desc', $s->getDirection('serial'));

        $s->sort();
        self::assertEquals('desc', $s->getDirection('created_at'));
    }
}
