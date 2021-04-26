<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

final class ColumnSorter
{
    private AttributeMapper $mapper;

    public function __construct(
        private Builder $builder,
        array $attributesMap = [],
        private array $default = [],
        private array $final = [],
    ) {
        $this->mapper = new AttributeMapper($attributesMap);
    }

    public function sort(?string $sorting = null, ?string $ordering = null) : Builder
    {
        if (isset($sorting)) {
            $this->useRequestedSort($sorting, $ordering);
        } else {
            $this->useInternalSort($this->default);
        }

        $this->useInternalSort($this->final);

        return $this->builder;
    }

    private function useRequestedSort(string $sorting, ?string $ordering) : void
    {
        $sortAttributes = explode(',', $sorting);
        foreach ($sortAttributes as $attribute) {
            if (str_starts_with($attribute, '-')) {
                $attribute = substr($attribute, 1);
                $order = 'desc';
            }
            $direction = $this->defineDirection($ordering ?? $order ?? 'asc');
            $columns = $this->mapper->resolve($attribute);
            foreach ($columns as $column) {
                $this->builder->orderBy(new Expression($column), $direction);
            }
        }
    }

    private function useInternalSort(array $default) : void
    {
        foreach ($default as $key => $value) {
            if (is_string($key)) {
                $direction = $this->defineDirection($value);
                $this->builder->orderBy(new Expression($key), $direction);
            } else {
                $this->builder->orderBy(new Expression($value));
            }
        }
    }

    private function defineDirection(string $direction) : string
    {
        return $direction === 'desc' ? 'desc' : 'asc';
    }
}
