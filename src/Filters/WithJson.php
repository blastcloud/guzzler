<?php

namespace BlastCloud\Guzzler\Filters;


use BlastCloud\Guzzler\Interfaces\With;

class WithJson extends Base implements With
{
    /** @var string */
    protected $json = '';
    protected $exclusive = false;

    public function withJson(array $json, bool $exclusive = false)
    {
        $this->exclusive = $exclusive;
        // Pre-sort so it only needs to be done once.
        $this->json = $json;
        $this->sort($json);
    }

    protected function isAssoc(array $arr)
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected function sort(&$array) {
        foreach ($array as &$value) {
            if (is_array($value)) $this->sort($value);
        }
        return $this->isAssoc($array)
            ? ksort($array)
            : sort($array);
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
                : strpos($j1, trim($j2, '{}')) !== false;
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "JSON: (Exclusive: {$e}) "
            .json_encode($this->json, JSON_PRETTY_PRINT);
    }
}