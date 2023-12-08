<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\Condition;
use Papalapa\Laravel\QueryFilter\ConditionHandler;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    public function test(): void
    {
        $handler = new ConditionHandler();

        $condition = new Condition('something');
        $handler->handle($condition);
        self::assertFalse($condition->isNull());
        self::assertFalse($condition->isNotNull());

        $condition = new Condition('~');
        $handler->handle($condition);
        self::assertFalse($condition->isNull());
        self::assertTrue($condition->isNotNull());

        $condition = new Condition(null);
        $handler->handle($condition);
        self::assertTrue($condition->isNull());
        self::assertFalse($condition->isNotNull());
    }
}
