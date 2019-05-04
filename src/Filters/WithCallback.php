<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Interfaces\With;

class WithCallback extends Base implements With
{
    /** @var \Closure */
    protected $closure;

    public function withCallback(\Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, $this->closure);
    }

    public function __toString(): string
    {
        return "Custom callback: \Closure";
    }
}