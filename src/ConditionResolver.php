<?php

namespace Papalapa\Laravel\QueryFilter;

final class ConditionResolver
{
    public function __construct(
        private string $value,
        private string $operator = '=',
    ) {
        $this->process($this->value);
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): string
    {
        return $this->value;
    }

    private function process(?string $value): void
    {
        if (preg_match('/^(<>|>=|!=|<=|>|=|<|!|~|\*|\^|\$)/', $value, $matches)) {
            $operator = $matches[1];
            $value = substr($value, strlen($operator));
            if ($operator === '!') {
                $this->operator = 'NOT LIKE';
                $this->value = "%{$value}%";
            } elseif ($operator === '~') {
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
