<?php

namespace BlastCloud\Guzzler\Traits;

use BlastCloud\Guzzler\Helpers\Disposition;
use GuzzleHttp\Psr7\MultipartStream;

trait Helpers
{
    /**
     * Given an associative array of $fields, this method searches through the
     * $parsed array, verifying that the passed $fields both exist and match
     * the provided values. By default, it does not care if there are extra
     * fields, but passing true as $exclusive will cause extra fields to
     * force a false to return.
     *
     * @param array $fields
     * @param array $parsed
     * @param bool $exclusive
     * @return bool
     */
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

    /**
     * Returns true if the $haystack passed does not have the $key or it
     * does, and the value does not match $value.
     *
     * @param string $key
     * @param mixed $value
     * @param array $haystack
     * @return bool
     */
    public function arrayMissing(string $key, $value, array $haystack)
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

    /**
     * Using the MultipartStream, split all fields and values into an array
     *
     * @param MultipartStream $stream
     * @return array
     */
    protected function parseMultipartBody(MultipartStream $stream): array
    {
        // Split based on the boundary and any dashes Guzzle adds
        $split = preg_split("/-*\b{$stream->getBoundary()}\b-*/", $stream->getContents(), 0, PREG_SPLIT_NO_EMPTY);

        // This is done so that any other filters or expectations passed
        // this reference will still see the body. Otherwise, an empty
        // string is returned on any further getContents() calls.
        $stream->rewind();

        // Trim line breaks and delete empty values
        $dispositions = array_filter(array_map(function ($dis) { return trim($dis);}, $split));

        // Parse out the parts into keys and values
        return array_map(function ($item) {
            return new Disposition($item);
        }, $dispositions);
    }
}