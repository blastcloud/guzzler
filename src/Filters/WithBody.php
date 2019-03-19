<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;

class WithBody extends Base implements With
{
    protected $body;

    public function withBody($body)
    {
        $this->body = $body;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            return $call['request']->getBody() == $this->body;
        });
    }

    public function __toString(): string
    {
        return str_pad('Body:', self::STR_PAD) . $this->body;
    }
}