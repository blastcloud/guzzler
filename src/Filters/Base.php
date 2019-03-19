<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Expectation;

abstract class Base
{
    const STR_PAD = 10;

    public function add($name, array $args)
    {
        if (!method_exists($this, $name)) {
            throw new \Error(sprintf("Call to undefined method %s::%s()", Expectation::class, $name));
        }

        call_user_func_array([$this, $name], $args);
    }
}