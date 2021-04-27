<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

/**
 * Allowed filter format (json|array):
 * -----------------------------------
 * {
 *      "and": [
 *          {"name": "*Frank"},
 *          {"is null": "deleted_at"},
 *          {"is not null": "logon_at"},
 *          {
 *              "or": [
 *                  {"role": "~admin"},
 *                  {"active": true}
 *              ]
 *          }
 *      ]
 * }
 * -----------------------------------
 */
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
                    $builder->where($column, '=', $value);
                }
            } elseif ($key === 'is null') {
                $columns = $this->mapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNull($column);
                }
            } elseif ($key === 'is not null') {
                $columns = $this->mapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNotNull($column);
                }
            } else {
                $columns = $this->mapper->resolve($key);
                $builder->where(function (Builder $builder) use ($columns, $value) {
                    $condition = new ConditionResolver((string)$value);
                    foreach ($columns as $column) {
                        $builder->orWhere(
                            column: new Expression($column),
                            operator: $condition->operator(),
                            value: $condition->value(),
                        );
                    }
                });
            }
        }
    }
}
