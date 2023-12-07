<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class BaseDataProvider
{
    protected const ATTRIBUTE_HAS = '_has';

    protected const ATTRIBUTE_COUNT = '_count';

    protected const ATTRIBUTE_WITH = '_with';

    protected const ATTRIBUTE_FILTER = '_filter';

    protected const ATTRIBUTE_SORT = '_sort';

    protected const ATTRIBUTE_ORDER = '_order';

    protected const ATTRIBUTE_PAGE = '_page';

    protected const ATTRIBUTE_LIMIT = '_limit';

    protected const DEFAULT_DELIMITER = ',';

    protected const SORT_ASC = ColumnSorter::SORT_ASC;

    protected const SORT_DESC = ColumnSorter::SORT_DESC;

    protected const SORT_INVERSION = ColumnSorter::DIRECTION_INVERSION;

    protected int $defaultPageNumber = 1;

    protected int $defaultPerPageLimit = 10;

    protected array $allowedFilter = [];

    protected array $allowedSort = [];

    protected array $defaultSort = [];

    protected array $finalSort = [];

    protected array $allowedHaving = [];

    protected array $allowedRelations = [];

    protected array $allowedCounts = [];

    private ?Builder $builder = null;

    final public function __construct(
        protected Request $request,
        private Paginator $paginator,
        private ConditionApplier $conditionApplier,
        private ColumnSorter $columnSorter,
    ) {
    }

    abstract protected function makeBuilder(): Builder;

    final public function builder(): Builder
    {
        if ($this->builder === null) {
            $this->builder = $this->makeBuilder();
        }

        return $this->builder;
    }

    final public function all(): Collection
    {
        $this->handleRequest($this->request);

        return Collection::make($this->builder()->get());
    }

    final public function paginated(): LengthAwarePaginator
    {
        $this->handleRequest($this->request);

        return $this->paginator
            ->setPageNumber(
                value: $this->request->input(static::ATTRIBUTE_PAGE),
                default: $this->defaultPageNumber,
            )
            ->setPageLimit(
                value: $this->request->input(static::ATTRIBUTE_LIMIT),
                default: $this->defaultPerPageLimit,
            )
            ->paginate(
                builder: $this->builder(),
                pageName: static::ATTRIBUTE_PAGE,
            );
    }

    private function handleRequest(Request $request): void
    {
        $this->withHaving($request->input(static::ATTRIBUTE_HAS));
        $this->withRelations($request->input(static::ATTRIBUTE_WITH));
        $this->withCounts($request->input(static::ATTRIBUTE_COUNT));

        $this->applyFilterConditions($request->input(static::ATTRIBUTE_FILTER));
        $this->applyFilterSorting(
            sort: $request->input(static::ATTRIBUTE_SORT),
            order: $request->input(static::ATTRIBUTE_ORDER),
        );
    }

    private function withHaving(mixed $requested): void
    {
        if (isset($requested) && is_string($requested) && count($this->allowedHaving)) {
            $having  = explode(static::DEFAULT_DELIMITER, $requested);
            $having  = array_intersect($this->allowedHaving, $having);
            $builder = $this->builder();
            if (method_exists($builder, 'has')) {
                foreach ($having as $relation) {
                    $builder->has($relation);
                }
            }
        }
    }

    private function withRelations(mixed $requested): void
    {
        if (isset($requested) && is_string($requested) && count($this->allowedRelations)) {
            $relations = explode(static::DEFAULT_DELIMITER, $requested);
            $relations = array_intersect($this->allowedRelations, $relations);
            $builder   = $this->builder();
            if (method_exists($builder, 'with')) {
                $builder->with($relations);
            }
        }
    }

    private function withCounts(mixed $requested): void
    {
        if (isset($requested) && is_string($requested) && count($this->allowedCounts)) {
            $counts  = explode(static::DEFAULT_DELIMITER, $requested);
            $counts  = array_intersect($this->allowedCounts, $counts);
            $builder = $this->builder();
            if (method_exists($builder, 'withCount')) {
                $builder->withCount($counts);
            }
        }
    }

    private function applyFilterConditions(mixed $filter): void
    {
        if (isset($filter) && (is_string($filter) || is_array($filter))) {
            $this->conditionApplier
                ->useMap($this->allowedFilter)
                ->filter(
                    builder: $this->builder(),
                    filter: $filter,
                );
        }
    }

    private function applyFilterSorting(mixed $sort, mixed $order): void
    {
        $this->columnSorter
            ->useFlags(
                asc: self::SORT_ASC,
                desc: self::SORT_DESC,
                inversion: self::SORT_INVERSION,
            )
            ->useMap($this->allowedSort)
            ->setDefaultSorting($this->defaultSort)
            ->setFinalSorting($this->finalSort)
            ->sort(
                requestedSorting: $sort,
                requestedOrdering: $order,
                columnDelimiter: static::DEFAULT_DELIMITER,
            )
            ->apply($this->builder());
    }
}
