<?php

namespace Papalapa\Laravel\QueryFilter;

class AttributeMapper
{
    private array $map = [];

    public function load(array $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function resolve(string $attribute): array
    {
        if (array_key_exists($attribute, $this->map)) {
            return (array) $this->map[$attribute];
        }

        if (in_array($attribute, $this->map, true)) {
            return (array) $attribute;
        }

        return [];
    }
}
