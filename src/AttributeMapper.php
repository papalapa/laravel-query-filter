<?php

namespace Papalapa\Laravel\QueryFilter;

final class AttributeMapper
{
    public function __construct(
        private array $map,
    ) {
    }

    public function resolve(string $attribute): array
    {
        if (array_key_exists($attribute, $this->map)) {
            return (array)$this->map[$attribute];
        }

        if (in_array($attribute, $this->map, true)) {
            return (array)$attribute;
        }

        return [];
    }
}
