<?php

namespace BlastCloud\Guzzler\Traits;

use BlastCloud\Guzzler\Helpers\Disposition;
use GuzzleHttp\Psr7\MultipartStream;

/**
 * Trait StreamFixer
 *
 * I really hate that this trait has to exist, but essentially it has to because
 * streams in PHP can only be read once. What this means is, any multipart forms
 * used in a request can only have their dispositions parsed once, no matter how
 * many assertions or expectations are made. So, forced to have a global parsing
 * of dispositions once before any expectations or assertions are run, and every
 * filter just has to KNOW about it. Going to have to document this suckage very
 * well.
 */
trait StreamFixer
{
    protected $history = [];
    protected $parsedDispositions = false;

    protected function parseDispositions()
    {
        if ($this->parsedDispositions) {
            return;
        }

        $this->history = array_map(function ($history) {
            $body = $history['request']->getBody();

            if ($body instanceof MultipartStream) {
                foreach ($this->parseMultipartBody($body) as $d) {
                    $history['dispositions'][$d->name] = $d;
                }
            }

            return $history;
        }, $this->history);

        $this->parsedDispositions = true;
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

        // Trim line breaks and delete empty values
        $dispositions = array_filter(array_map(function ($dis) { return trim($dis);}, $split));

        // Parse out the parts into keys and values
        return array_map(function ($item) {
            return new Disposition($item);
        }, $dispositions);
    }
}