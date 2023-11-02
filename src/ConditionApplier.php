<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

final class ConditionApplier
{
    private AttributeMapper $mapper;

    public function __construct(array $attributesMap = [])
    {
        $this->mapper = new AttributeMapper($attributesMap);
    }

    public function filter(Builder $builder, mixed $filter): Builder
    {
        $normalizer = new FilterNormalizer();
        $data = $normalizer->normalize($filter);

        return $builder->where(function (Builder $builder) use ($data) {
            $this->applyConditions($builder, $data);
        });
    }

    private function applyConditions(Builder $builder, array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if ($key === 'and') {
                        $builder->where(function (Builder $builder) use ($item) {
                            $this->applyConditions($builder, $item);
                        });
                    } elseif ($key === 'or') {
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
            } elseif ($key === 'is null') {
                $columns = $this->mapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNull(new Expression($column));
                }
            } elseif ($key === 'is not null') {
                $columns = $this->mapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNotNull(new Expression($column));
                }
            } else {
                $columns = $this->mapper->resolve($key);
                $builder->where(function (Builder $builder) use ($columns, $value) {
                    $condition = new ConditionResolver($value);
                    foreach ($columns as $column) {
                        if ($condition->isNull()) {
                            $builder->orWhereNull(new Expression($column));
                        } elseif ($condition->isNotNull()) {
                            $builder->orWhereNotNull(new Expression($column));
                        } else {
                            $builder->orWhere(
                                column: new Expression($column),
                                operator: $condition->operator(),
                                value: $condition->value(),
                            );
                        }
                    }
                });
            }
        }
    }
}
