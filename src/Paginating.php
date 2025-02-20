<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Paginating
{
    private int $pageNumber;

    private int $pageLimit;

    final public function setPageNumber(mixed $value, int $default): static
    {
        $this->pageNumber = $this->validatePositiveValue($value, $default);

        return $this;
    }

    final public function setPageLimit(mixed $value, int $default, int $max): static
    {
        $this->pageLimit = $this->validatePositiveValue($value, $default);
        $this->pageLimit = min($this->pageLimit, $max);

        return $this;
    }

    private function validatePositiveValue(mixed $value, int $default): int
    {
        if (is_numeric($value)) {
            return max((int) $value, 0) ?: $default;
        }

        return $default;
    }

    public function paginate(Builder $builder, string $pageName): LengthAwarePaginator
    {
        if (false === isset($this->pageNumber, $this->pageLimit)) {
            throw new \LogicException('Attributes pageNumber and pageLimit must be configured');
        }

        if ($this->pageNumber > 1) {
            /**
             * Seems that `$total = $this->builder->count();` can be used as count of results.
             * But it does not work if using `groupBy(...)`. And still works without grouping.
             */
            $total            = DB::connection()->table($builder->getQuery(), 'aggregate')->count();
            $this->pageNumber = (int) min(ceil($total / $this->pageLimit), $this->pageNumber);
        }

        return $builder->paginate($this->pageLimit, ['*'], $pageName, $this->pageNumber);
    }
}
