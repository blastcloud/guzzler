<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Filters\Base;

class WithHeader extends Base implements With
{
    protected $headers = [];

    public function withHeaders($values)
    {
        foreach ($values as $key => $value) {
            $this->withHeader($key, $value);
        }
    }

    public function withHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function __toString(): string
    {
        return str_pad("Headers: ", self::STR_PAD)
            .json_encode($this->headers, JSON_PRETTY_PRINT);
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            foreach ($this->headers as $key => $value) {
                $header = $call['request']->getHeader($key);

                if ($header != $value && !in_array($value, $header)) {
                    return false;
                }
            }

            return true;
        });
    }

}