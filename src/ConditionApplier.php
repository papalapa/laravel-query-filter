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
    private AttributeMapper $attributeMapper;

    private FilterNormalizer $filterNormalizer;

    public function __construct(
        private Builder $builder,
        array $attributesMap = [],
    ) {
        $this->attributeMapper = new AttributeMapper($attributesMap);
        $this->filterNormalizer = new FilterNormalizer();
    }

    public function filter(mixed $filter): void
    {
        $filterData = $this->filterNormalizer->normalize($filter);
        $this->builder->where(function (Builder $builder) use ($filterData) {
            $this->applyConditions($builder, $filterData);
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
            } elseif ($key === 'is null') {
                $columns = $this->attributeMapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNull($column);
                }
            } elseif ($key === 'is not null') {
                $columns = $this->attributeMapper->resolve($value);
                foreach ($columns as $column) {
                    $builder->whereNotNull($column);
                }
            } else {
                $columns = $this->attributeMapper->resolve($key);
                $builder->where(function (Builder $builder) use ($columns, $value) {
                    foreach ($columns as $column) {
                        $builder->orWhere(... $this->makeCondition($column, (string)$value));
                    }
                });
            }
        }
    }

    private function makeCondition(string $attribute, string $value): array
    {
        if (preg_match('/^(<>|>=|!=|<=|>|=|<|!|~|\*|\^|\$)/', $value, $matches)) {
            $operator = $matches[1];
            $value = substr($value, strlen($operator));
            if ($operator === '~') {
                $operator = 'NOT LIKE';
                $value = "%{$value}%";
            } elseif ($operator === '*') {
                $operator = 'LIKE';
                $value = "%{$value}%";
            } elseif ($operator === '^') {
                $operator = 'LIKE';
                $value = "{$value}%";
            } elseif ($operator === '$') {
                $operator = 'LIKE';
                $value = "%{$value}";
            }
        }

        return [new Expression($attribute), $operator ?? '=', $value];
    }
}
