<?php

namespace Papalapa\Laravel\QueryFilter;

final class ConditionResolver
{
    public const IS_NULL = 'IS';

    public const IS_NOT_NULL = 'IS NOT';

    public function __construct(
        private ?string $value,
        private string $operator = '=',
    ) {
        $this->process($this->value);
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function isNull(): bool
    {
        return $this->operator === self::IS_NULL;
    }

    public function isNotNull(): bool
    {
        return $this->operator === self::IS_NOT_NULL;
    }

    public function value(): ?string
    {
        return $this->value;
    }

    private function process(?string $value): void
    {
        if (is_null($value)) {
            $this->operator = self::IS_NULL;
            return;
        }

        if ($value === '~') {
            $this->operator = self::IS_NOT_NULL;
            $this->value = null;
            return;
        }

        if (preg_match('/^(<>|>=|!=|<=|>|=|<|!|\*|\^|\$)/', $value, $matches)) {
            $operator = $matches[1];
            $value = substr($value, strlen($operator));
            if ($operator === '!') {
                $this->operator = 'NOT LIKE';
                $this->value = "%{$value}%";
            } elseif ($operator === '*') {
                $this->operator = 'LIKE';
                $this->value = "%{$value}%";
            } elseif ($operator === '^') {
                $this->operator = 'LIKE';
                $this->value = "{$value}%";
            } elseif ($operator === '$') {
                $this->operator = 'LIKE';
                $this->value = "%{$value}";
            } else {
                $this->operator = $operator;
                $this->value = $value;
            }
        }
    }
}
