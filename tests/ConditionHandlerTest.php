<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\Condition;
use Papalapa\Laravel\QueryFilter\ConditionHandler;
use PHPUnit\Framework\TestCase;

class ConditionHandlerTest extends TestCase
{
    public function test(): void
    {
        $handler = new ConditionHandler();

        $operators = ['<>', '>=', '!=', '<=', '>', '=', '<'];
        foreach ($operators as $operator) {
            $condition = new Condition("{$operator}username");
            $handler->handle($condition);
            self::assertEquals($operator, $condition->operator);
            self::assertEquals('username', $condition->value);
        }

        $condition = new Condition(null);
        $handler->handle($condition);
        self::assertEquals('IS', $condition->operator);
        self::assertEquals(null, $condition->value);

        $condition = new Condition('~');
        $handler->handle($condition);
        self::assertEquals('IS NOT', $condition->operator);
        self::assertEquals(null, $condition->value);

        $condition = new Condition('!username');
        $handler->handle($condition);
        self::assertEquals('NOT LIKE', $condition->operator);
        self::assertEquals('%username%', $condition->value);

        $condition = new Condition('*username');
        $handler->handle($condition);
        self::assertEquals('LIKE', $condition->operator);
        self::assertEquals('%username%', $condition->value);

        $condition = new Condition('^username');
        $handler->handle($condition);
        self::assertEquals('LIKE', $condition->operator);
        self::assertEquals('username%', $condition->value);

        $condition = new Condition('$username');
        $handler->handle($condition);
        self::assertEquals('LIKE', $condition->operator);
        self::assertEquals('%username', $condition->value);
    }
}
