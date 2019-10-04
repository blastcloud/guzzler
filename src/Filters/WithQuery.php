<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Traits\Helpers;
use BlastCloud\Chassis\Filters\Base;

class WithQuery extends Base implements With
{
    use Helpers;

    protected $query = [];
    protected $exclusive = false;

    public function withQuery(array $values, bool $exclusive = false)
    {
        foreach ($values as $key => $value) {
            $this->query[$key] = $value;
        }

        $this->exclusive = $exclusive;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            parse_str($call['request']->getUri()->getQuery(), $parsed);
            return $this->verifyFields($this->query, $parsed, $this->exclusive);
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "Query: (Exclusive: {$e})".json_encode($this->query, JSON_PRETTY_PRINT);
    }
}