<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class ColumnSorter
{
    public const SORT_ASC = 'asc';

    public const SORT_DESC = 'desc';

    public const DIRECTION_INVERSION = '-';

    private string $asc = self::SORT_ASC;

    private string $desc = self::SORT_DESC;

    private string $inversion = self::DIRECTION_INVERSION;

    private array $defaultSorting = [];

    private array $finalSorting = [];

    private Collection $sorting;

    public function __construct(private AttributeMapper $mapper)
    {
    }

    public function useMap(array $map): self
    {
        $this->mapper->load($map);

        return $this;
    }

    public function useFlags(
        string $asc = self::SORT_ASC,
        string $desc = self::SORT_DESC,
        string $inversion = self::DIRECTION_INVERSION,
    ): self {
        $this->asc       = $asc;
        $this->desc      = $desc;
        $this->inversion = $inversion;

        return $this;
    }

    public function setDefaultSorting(array $data): self
    {
        $this->defaultSorting = $data;

        return $this;
    }

    public function setFinalSorting(array $data): self
    {
        $this->finalSorting = $data;

        return $this;
    }

    public function sort(
        ?string $requestedSorting = null,
        ?string $requestedOrdering = null,
        string $columnDelimiter = ','
    ): self {
        $this->sorting = new Collection();

        if (isset($requestedSorting)) {
            $this->useRequestedSort($requestedSorting, $requestedOrdering, $columnDelimiter);
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
            if (str_starts_with($attribute, $this->inversion)) {
                $attribute = substr($attribute, 1);
                $order     = $this->desc;
            }
            $columns   = $this->mapper->resolve($attribute);
            $direction = $this->defineDirection($ordering ?? $order ?? $this->asc);
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
                $this->sorting->put($value, $this->asc);
            }
        }
    }

    private function defineDirection(string $direction): string
    {
        return ($direction === $this->desc) ? $this->desc : $this->asc;
    }
}
