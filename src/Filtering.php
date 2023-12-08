<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

class Filtering
{
    protected const AND_OPERATOR = 'and';

    protected const OR_OPERATOR = 'or';

    protected const IS_NULL_OPERATOR = 'is null';

    protected const IS_NOT_NULL_OPERATOR = 'is not null';

    final public function __construct(
        private AttributeMapper $mapper,
        private ConditionHandler $conditionHandler,
    ) {
    }

    final public function useMap(array $map): static
    {
        $this->mapper->load($map);

        return $this;
    }

    final public function filter(Builder $builder, array $conditions): Builder
    {
        return $builder->where(function (Builder $builder) use ($conditions) {
            $this->applyConditions($builder, $conditions);
        });
    }

    private function applyConditions(Builder $builder, array $conditions): void
    {
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if ($key === static::AND_OPERATOR) {
                        $builder->where(function (Builder $builder) use ($item) {
                            $this->applyConditions($builder, $item);
                        });
                    } elseif ($key === static::OR_OPERATOR) {
                        $builder->orWhere(function (Builder $builder) use ($item) {
                            $this->applyConditions($builder, $item);
                        });
                    }
                }
            } elseif (is_bool($value)) {
                $columns = $this->mapper->resolve($key);
                foreach ($columns as $column) {
                    $builder->where(new Expression($column), '=', $value);
                }
            } elseif ($key === static::IS_NULL_OPERATOR) {
                $columns = $this->mapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNull(new Expression($column));
                }
            } elseif ($key === static::IS_NOT_NULL_OPERATOR) {
                $columns = $this->mapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNotNull(new Expression($column));
                }
            } else {
                $columns = $this->mapper->resolve($key);
                $builder->where(function (Builder $builder) use ($columns, $value) {
                    $condition = new Condition($value);
                    $this->conditionHandler->handle($condition);
                    foreach ($columns as $column) {
                        if ($condition->isNull()) {
                            $builder->orWhereNull(new Expression($column));
                        } elseif ($condition->isNotNull()) {
                            $builder->orWhereNotNull(new Expression($column));
                        } else {
                            $builder->orWhere(
                                column: new Expression($column),
                                operator: $condition->operator,
                                value: $condition->value,
                            );
                        }
                    }
                });
            }
        }
    }
}
