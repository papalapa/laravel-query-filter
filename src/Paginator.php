<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class Paginator
{
    public function __construct(
        private Builder $builder,
        private int $defaultPageNumber,
        private int $defaultPerPageLimit
    ) {
    }

    public function paginate(mixed $limit, mixed $page) : LengthAwarePaginator
    {
        $limit = $this->resolveLimit($limit);
        $page = $this->resolvePage($page);

        if ($page > 1) {
            /**
             * Seems that `$total = $this->builder->count();` can be used as count of results.
             * But it does not work if using `groupBy(...)`. And still works without grouping.
             */
            $total = DB::connection()->table($this->builder->getQuery())->count();
            $page = (int)min(ceil($total / $limit), $page);
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
