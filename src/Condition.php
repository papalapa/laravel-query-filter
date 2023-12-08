<?php

namespace Papalapa\Laravel\QueryFilter;

final class Condition
{
    private ?bool $null = null;

    public function __construct(
        public ?string $value,
        public string $operator = '=',
    ) {
    }

    public function setNull(bool $null): void
    {
        $this->null = $null;
    }

    public function isNull(): bool
    {
        return $this->null === true;
    }

    public function isNotNull(): bool
    {
        return $this->null === false;
    }
}
