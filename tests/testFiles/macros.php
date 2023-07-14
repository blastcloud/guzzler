<?php

use BlastCloud\Guzzler\Expectation;

Expectation::macro('fromFile', function (Expectation $e, $url) {
    return $e->post($url);
});