<?php

namespace Papalapa\Laravel\QueryFilter\Tests;

use Papalapa\Laravel\QueryFilter\ConditionResolver;
use PHPUnit\Framework\TestCase;

class ConditionResolverTest extends TestCase
{
    public function test(): void
    {
        $operators = ['<>', '>=', '!=', '<=', '>', '=', '<'];
        foreach ($operators as $operator) {
            $resolver = new ConditionResolver("{$operator}username");
            self::assertEquals($operator, $resolver->operator());
            self::assertEquals('username', $resolver->value());
        }

        $resolver = new ConditionResolver('!username');
        self::assertEquals('NOT LIKE', $resolver->operator());
        self::assertEquals('%username%', $resolver->value());

        $resolver = new ConditionResolver('~username');
        self::assertEquals('NOT LIKE', $resolver->operator());
        self::assertEquals('%username%', $resolver->value());

        $resolver = new ConditionResolver('*username');
        self::assertEquals('LIKE', $resolver->operator());
        self::assertEquals('%username%', $resolver->value());

        $resolver = new ConditionResolver('^username');
        self::assertEquals('LIKE', $resolver->operator());
        self::assertEquals('username%', $resolver->value());

        $resolver = new ConditionResolver('$username');
        self::assertEquals('LIKE', $resolver->operator());
        self::assertEquals('%username', $resolver->value());
    }
}
