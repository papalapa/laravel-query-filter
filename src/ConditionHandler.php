<?php

namespace Papalapa\Laravel\QueryFilter;

class ConditionHandler
{
    protected const SQL_IS = 'IS';

    protected const SQL_IS_NOT = 'IS NOT';

    protected const SQL_LIKE = 'LIKE';

    protected const SQL_NOT_LIKE = 'NOT LIKE';

    public function handle(Condition $condition): void
    {
        if (is_null($condition->value)) {
            $condition->operator = static::SQL_IS;
            $condition->value    = null;
            $condition->setNull(true);
        } elseif ($condition->value === '~') {
            $condition->operator = static::SQL_IS_NOT;
            $condition->value    = null;
            $condition->setNull(false);
        } elseif (preg_match('/^(<>|>=|!=|<=|>|=|<|!|\*|\^|\$).+/', $condition->value, $matches)) {
            $operator         = $matches[1];
            $condition->value = substr($condition->value, strlen($operator));
            if ($operator === '*') {
                $condition->operator = static::SQL_LIKE;
                $condition->value    = "%{$condition->value}%";
            } elseif ($operator === '!') {
                $condition->operator = static::SQL_NOT_LIKE;
                $condition->value    = "%{$condition->value}%";
            } elseif ($operator === '^') {
                $condition->operator = static::SQL_LIKE;
                $condition->value    = "{$condition->value}%";
            } elseif ($operator === '$') {
                $condition->operator = static::SQL_LIKE;
                $condition->value    = "%{$condition->value}";
            } else {
                $condition->operator = $operator;
            }
        }
    }
}
