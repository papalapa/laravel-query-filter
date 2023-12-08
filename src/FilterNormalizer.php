<?php

namespace Papalapa\Laravel\QueryFilter;

class FilterNormalizer
{
    public function normalize(mixed $filter) : array
    {
        $data = [];

        if (is_string($filter)) {
            try {
                $data = json_decode($filter, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $data = [];
            }
        } elseif (is_array($filter)) {
            $data = $filter;
        }

        return $data;
    }
}
