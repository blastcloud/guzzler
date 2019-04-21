<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;
use GuzzleHttp\Psr7\Uri;

class WithEndpoint extends Base implements With
{
    public $endpoint;
    protected $method;

    public function withEndpoint(string $uri, string $method)
    {
        $this->endpoint = Uri::fromParts(parse_url($uri));
        $this->method = $method;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            return $call['request']->getMethod() == $this->method
                && $call['request']->getUri()->getPath() == $this->endpoint->getPath();
        });
    }

    public function __toString(): string
    {
        return str_pad('Method:', self::STR_PAD).$this->method;
    }
}