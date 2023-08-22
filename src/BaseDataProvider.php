<?php

namespace Papalapa\Laravel\QueryFilter;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class BaseDataProvider
{
    private const ATTRIBUTE_HAS = '_has';

    private const ATTRIBUTE_COUNT = '_count';

    private const ATTRIBUTE_WITH = '_with';

    private const ATTRIBUTE_FILTER = '_filter';

    private const ATTRIBUTE_SORT = '_sort';

    private const ATTRIBUTE_ORDER = '_order';

    private const ATTRIBUTE_PAGE = '_page';

    private const ATTRIBUTE_LIMIT = '_limit';

    protected int $defaultPageNumber = 1;

    protected int $defaultPerPageLimit = 10;

    protected array $allowedFilter = [];

    protected array $allowedSort = [];

    protected array $defaultSort = [];

    protected array $finalSort = [];

    protected array $allowedHaving = [];

    protected array $allowedRelations = [];

    protected array $allowedCounts = [];

    private ?EloquentBuilder $builder = null;

    public function __construct(protected Request $request)
    {
    }

    abstract protected function makeBuilder(): EloquentBuilder;

    final public function builder(): EloquentBuilder
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

        $paginator = new Paginator(
            builder: $this->builder(),
            defaultPageNumber: $this->defaultPageNumber,
            defaultPerPageLimit: $this->defaultPerPageLimit
        );

        return $paginator->paginate(
            limit: $this->request->get(self::ATTRIBUTE_LIMIT),
            page: $this->request->get(self::ATTRIBUTE_PAGE)
        );
    }

    private function handleRequest(Request $request): void
    {
        $this->withHaving($request->get(self::ATTRIBUTE_HAS));
        $this->withRelations($request->get(self::ATTRIBUTE_WITH));
        $this->withCounts($request->get(self::ATTRIBUTE_COUNT));
        $this->applyFilterConditions($request->get(self::ATTRIBUTE_FILTER));
        $this->applyFilterSorting(
            sort: $request->get(self::ATTRIBUTE_SORT),
            order: $request->get(self::ATTRIBUTE_ORDER)
        );
    }

    private function withHaving(?string $requested, string $separator = ','): void
    {
        if (isset($requested) && count($this->allowedHaving)) {
            $having = explode($separator, $requested);
            $having = array_intersect($this->allowedHaving, $having);
            foreach ($having as $relation) {
                $this->builder()->has($relation);
            }
        }
    }

    private function withRelations(?string $requested, string $separator = ','): void
    {
        if (isset($requested) && count($this->allowedRelations)) {
            $relations = explode($separator, $requested);
            $relations = array_intersect($this->allowedRelations, $relations);
            $this->builder()->with($relations);
        }
    }

    private function withCounts(?string $requested, string $separator = ','): void
    {
        if (isset($requested) && count($this->allowedCounts)) {
            $counts = explode($separator, $requested);
            $counts = array_intersect($this->allowedCounts, $counts);
            $this->builder()->withCount($counts);
        }
    }

    private function applyFilterConditions(mixed $filter): void
    {
        (new ConditionApplier(
            attributesMap: $this->allowedFilter
        ))->filter($this->builder(), $filter);
    }

    private function applyFilterSorting(mixed $sort, mixed $order): void
    {
        (new ColumnSorter(
            attributesMap: $this->allowedSort,
            defaultSorting: $this->defaultSort,
            finalSorting: $this->finalSort,
        ))->sort($sort, $order)->apply($this->builder());
    }
}
