<?php

namespace BlastCloud\Guzzler\Filters;


use BlastCloud\Guzzler\Interfaces\With;

class WithJson extends Base implements With
{
    protected $json = [];
    protected $exclusive = false;

    public function withJson(array $json, bool $exclusive = false)
    {
        $this->json = $json;
        $this->exclusive = $exclusive;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            $body = json_decode($call['request']->getBody(), true);
            return $body == $this->json;
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "JSON: (Exclusive: {$e}) "
            .json_encode($this->json, JSON_PRETTY_PRINT);
    }
}