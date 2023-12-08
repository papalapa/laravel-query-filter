<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Paginating
{
    private int $pageNumber;

    private int $pageLimit;

    final public function setPageNumber(mixed $value, int $default): static
    {
        $this->pageNumber = $this->validatePositiveValue($value, $default);

        return $this;
    }

    final public function setPageLimit(mixed $value, int $default): static
    {
        $this->pageLimit = $this->validatePositiveValue($value, $default);

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
            $this->pageNumber = (int) min(ceil($builder->count() / $this->pageLimit), $this->pageNumber);
        }

        return $builder->paginate($this->pageLimit, ['*'], $pageName, $this->pageNumber);
    }
}
