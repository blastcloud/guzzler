<?php

namespace BlastCloud\Guzzler\Helpers;

use GuzzleHttp\Psr7\MultipartStream;

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
}