<?php

namespace BlastCloud\Guzzler\Traits;

trait RecursiveSort
{
    // Determine if the passed array has any non-incrementing keys; associative array
    protected function isAssoc(array $arr)
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    // Recursively sort by keys and values
    protected function sort(&$array) {
        foreach ($array as &$value) {
            if (is_array($value)) $this->sort($value);
        }

        return $this->isAssoc($array)
            ? ksort($array)
            : sort($array);
    }
}
