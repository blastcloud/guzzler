<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Filters\Base;

class WithBody extends Base implements With
{
    protected $body;
    protected $exclusive;

    public function withBody($body, bool $exclusive = false)
    {
        $this->body = $body;
        $this->exclusive = $exclusive;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            $body = $call['request']->getBody();

            return $this->exclusive
                ? $body == $this->body
                : strpos($body, $this->body) !== false;
        });
    }

    public function __toString(): string
    {
        return str_pad('Body:', self::STR_PAD) . $this->body;
    }
}