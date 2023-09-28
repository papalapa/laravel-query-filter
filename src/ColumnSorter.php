<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

final class ColumnSorter
{
    private const SORT_ASC = 'asc';

    private const SORT_DESC = 'desc';

    private const DIRECTION_INVERSION = '-';

    private AttributeMapper $mapper;

    private Collection $sorting;

    public function __construct(
        array $attributesMap = [],
        private array $defaultSorting = [],
        private array $finalSorting = [],
    ) {
        $this->mapper = new AttributeMapper($attributesMap);
    }

    public function sort(?string $sorting = null, ?string $ordering = null, string $delimiter = ','): self
    {
        $this->sorting = new Collection();

        if (isset($sorting)) {
            $this->useRequestedSort($sorting, $ordering, $delimiter);
        } else {
            $this->useInternalSort($this->defaultSorting);
        }
        $this->useInternalSort($this->finalSorting);

        return $this;
    }

    public function getDirection(string $attribute): ?string
    {
        if ($this->sorting->has($attribute)) {
            return $this->sorting->get($attribute);
        }

        return null;
    }

    public function apply(Builder $builder): Builder
    {
        foreach ($this->sorting as $column => $direction) {
            $builder->orderBy(new Expression($column), $direction);
        }

        return $builder;
    }

    private function useRequestedSort(string $sorting, ?string $ordering, string $delimiter): void
    {
        $attributes = explode($delimiter, $sorting);
        foreach ($attributes as $attribute) {
            if (str_starts_with($attribute, self::DIRECTION_INVERSION)) {
                $attribute = substr($attribute, 1);
                $order     = self::SORT_DESC;
            }
            $columns   = $this->mapper->resolve($attribute);
            $direction = $this->defineDirection($ordering ?? $order ?? self::SORT_ASC);
            foreach ($columns as $column) {
                $this->sorting->put($column, $direction);
            }
        }
    }

    private function useInternalSort(array $default): void
    {
        foreach ($default as $key => $value) {
            if (is_string($key)) {
                $this->sorting->put($key, $this->defineDirection($value));
            } else {
                $this->sorting->put($value, self::SORT_ASC);
            }
        }
    }

    private function defineDirection(string $direction): string
    {
        return ($direction === self::SORT_DESC) ? self::SORT_DESC : self::SORT_ASC;
    }
}
