<?php

namespace Papalapa\Laravel\QueryFilter;

class ConditionResolver
{
    protected const SQL_IS = 'IS';

    protected const SQL_IS_NOT = 'IS NOT';

    protected const SQL_LIKE = 'LIKE';

    protected const SQL_NOT_LIKE = 'NOT LIKE';

    public function __construct(
        private ?string $value,
        private string $operator = '=',
    ) {
        $this->process();
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    public function isNull(): bool
    {
        return $this->operator === self::SQL_IS;
    }

    public function isNotNull(): bool
    {
        return $this->operator === self::SQL_IS_NOT;
    }

    protected function process(): void
    {
        if (is_null($this->value)) {
            $this->handleNull();
        } elseif (preg_match('/^(<>|>=|!=|<=|>|=|<|~|!|\*|\^|\$)/', $this->value, $matches)) {
            $operator    = $matches[1];
            $this->value = substr($this->value, strlen($operator));
            if ($operator === '~') {
                $this->handleNotNull();
            } elseif ($operator === '*') {
                $this->handleLike();
            } elseif ($operator === '!') {
                $this->handleNotLike();
            } elseif ($operator === '^') {
                $this->handleStartsWith();
            } elseif ($operator === '$') {
                $this->handleEndsWith();
            } else {
                $this->operator = $operator;
            }
        }
    }

    private function handleNull(): void
    {
        $this->operator = self::SQL_IS;
        $this->value    = null;
    }

    private function handleNotNull(): void
    {
        $this->operator = self::SQL_IS_NOT;
        $this->value    = null;
    }

    private function handleLike(): void
    {
        $this->operator = self::SQL_LIKE;
        $this->value    = "%{$this->value}%";
    }

    private function handleNotLike(): void
    {
        $this->operator = self::SQL_NOT_LIKE;
        $this->value    = "%{$this->value}%";
    }

    private function handleStartsWith(): void
    {
        $this->operator = self::SQL_LIKE;
        $this->value    = "{$this->value}%";
    }

    private function handleEndsWith(): void
    {
        $this->operator = self::SQL_LIKE;
        $this->value    = "%{$this->value}";
    }
}
