<?php

namespace BlastCloud\Guzzler\Filters;

trait Helpers
{
    public function testFields(array $fields, array $parsed, $exclusive = false)
    {
        foreach ($fields as $key => $value) {
            if (
                !isset($parsed[$key])
                || (is_array($parsed[$key]) && !in_array($value, $parsed[$key]))
                || $parsed[$key] != $value
            ) {
                return false;
            }
        }

        // Only if "exclusive" flag is set to true.
        if ($exclusive && count($parsed) > count($fields)) {
            return false;
        }

        return true;
    }
}