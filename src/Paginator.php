<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;

final class Paginator
{
    public function __construct(
        private Builder $builder,
        private int $defaultPageNumber,
        private int $defaultPerPageLimit,
    ) {
    }

    public function paginate(mixed $limit, mixed $page) : LengthAwarePaginator
    {
        $limit = $this->resolveLimit($limit);
        $page = $this->resolvePage($page);

        if ($page > 1) {
            $page = (int)min(ceil($this->builder->count() / $limit), $page);
        }

        return $this->builder->paginate($limit, ['*'], '_page', $page);
    }

    private function resolveLimit(mixed $limit) : int
    {
        return max((int)$limit, 0) ?: $this->defaultPerPageLimit;
    }

    private function resolvePage(mixed $page) : int
    {
        return max((int)$page, 0) ?: $this->defaultPageNumber;
    }
}
