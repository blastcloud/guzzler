<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Filters\Base;
use BlastCloud\Guzzler\Traits\RecursiveSort;

class WithJson extends Base implements With
{
    use RecursiveSort;

    /** @var string */
    protected $json = '';
    protected $exclusive = false;

    public function withJson(array $json, bool $exclusive = false)
    {
        $this->exclusive = $exclusive;
        // Pre-sort so it only needs to be done once.
        $this->json = $json;
        $this->sort($this->json);
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            $body = json_decode($call['request']->getBody(), true);

            $this->sort($body);

            $j1 = json_encode($body);
            $j2 = json_encode($this->json);

            return $this->exclusive
                ? $j1 == $j2
                : strpos($j1, trim($j2, '{}[]')) !== false;
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "JSON: (Exclusive: {$e}) "
            .json_encode($this->json, JSON_PRETTY_PRINT);
    }
}
