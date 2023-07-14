<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Filters\Base;

class WithOption extends Base implements With
{
    protected $options = [];

    public function withOptions($values)
    {
        foreach ($values as $key => $value) {
            $this->withOption($key, $value);
        }
    }

    public function withOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {
            foreach ($this->options as $key => $value) {
                $option = $call['options'][$key] ?? null;

                if ($option !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    public function __toString(): string
    {
        return str_pad("Options: ", self::STR_PAD)
            .json_encode($this->options, JSON_PRETTY_PRINT);
    }
}