<?php

namespace BlastCloud\Guzzler\Traits;

trait Helpers
{
    public function testFields(array $fields, array $parsed, $exclusive = false)
    {
        foreach ($fields as $key => $value) {
            if ($this->arrayMissing($key, $value, $parsed)) {
                return false;
            }
        }

        // Only if "exclusive" flag is set to true.
        if ($exclusive && count($parsed) > count($fields)) {
            return false;
        }

        return true;
    }

    public function arrayMissing($key, $value, array $haystack)
    {
        return !isset($haystack[$key])
            || (is_array($haystack[$key]) && !in_array($value, $haystack[$key]))
            || $haystack[$key] != $value;
    }

    /**
     * Given an array of objects or arrays, return every instance of a
     * specified field. Any empty values are eliminated.
     *
     * @param array $collection
     * @param string $property
     * @return array
     */
    public function pluck(array $collection, string $property)
    {
        return array_filter(
            array_map(function ($item) use ($property) {
                return ((array)$item)[$property] ?? null;
            }, $collection)
        );
    }
}