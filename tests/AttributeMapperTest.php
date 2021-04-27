<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\AttributeMapper;
use PHPUnit\Framework\TestCase;

class AttributeMapperTest extends TestCase
{
    public function test(): void
    {
        $map = [
            /* field alias => real field name */
            'id' => 'users.id',
            'username' => 'users.username',
            'datetime' => 'clients.created_at',
        ];

        $mapper = new AttributeMapper($map);

        self::assertEquals(['users.id'], $mapper->resolve('id'));
        self::assertEquals(['users.username'], $mapper->resolve('username'));

        self::assertEquals([], $mapper->resolve('not_supported_field'));
    }
}
