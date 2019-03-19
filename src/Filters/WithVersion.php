<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;

class WithVersion extends Base implements With
{
    protected $protocol = 1.1;

    public function withVersion($version)
    {
        $this->protocol = $version;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            return $call['request']->getProtocolVersion() == $this->protocol;
        });
    }

    public function __toString(): string
    {
        return str_pad("Protocol: ", self::STR_PAD)
            .$this->protocol;
    }

}