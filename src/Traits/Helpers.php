<?php

namespace BlastCloud\Guzzler\Traits;

use GuzzleHttp\Psr7\MultipartStream;
use BlastCloud\Guzzler\Helpers\Disposition;

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

    /**
     * Using the MultipartStream, split all fields and values into an array
     *
     * @param MultipartStream $stream
     * @return array
     */
    public function parseMultipartBody(MultipartStream $stream): array
    {
        // Split based on the boundary and any dashes Guzzle adds
        $split = preg_split("/-*\b{$stream->getBoundary()}\b-*/", $stream->getContents(), 0, PREG_SPLIT_NO_EMPTY);

        // Trim line breaks and delete empty values
        $dispositions = array_filter(array_map(function ($dis) { return trim($dis);}, $split));

        // Parse out the parts into keys and values
        return array_map(function ($item) {
            return new Disposition($item);
        }, $dispositions);
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
                if (is_object($item)) {
                    return $item->$property ?? null;
                }

                return $item[$property] ?? null;
            }, $collection)
        );
    }
}