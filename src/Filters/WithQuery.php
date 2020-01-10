<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Traits\Helpers;
use BlastCloud\Chassis\Filters\Base;

class WithQuery extends Base implements With
{
    use Helpers;

    protected $query = [];
    protected $keys = [];
    protected $exclusive = false;

    public function withQuery(array $values, bool $exclusive = false)
    {
        foreach ($values as $key => $value) {
            $this->query[$key] = $value;
        }

        $this->exclusive = $exclusive;
    }

    public function withQueryKeys(array $keys)
    {
        $this->keys = $keys;
    }

    public function withQueryKey(string $key)
    {
        $this->keys[] = $key;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            parse_str($call['request']->getUri()->getQuery(), $parsed);

            if (array_diff($this->keys, array_keys($parsed))) {
                return false;
            }

            return $this->verifyFields($this->query, $parsed, $this->exclusive);
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "Query: (Exclusive: {$e})".json_encode($this->query, JSON_PRETTY_PRINT);
    }
}