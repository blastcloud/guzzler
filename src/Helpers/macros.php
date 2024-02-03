<?php

use BlastCloud\Guzzler\Expectation;

Expectation::macro('synchronous', function (BlastCloud\Chassis\Expectation $e) {
    return $e->withOption('synchronous', true);
});

Expectation::macro('asynchronous', function (BlastCloud\Chassis\Expectation $e) {
    // Set to null, because if the request was asynchronous, the
    // "synchronous" key is not set in the options array.
    return $e->withOption('synchronous', null);
});

foreach (Expectation::VERBS as $verb) { // @codeCoverageIgnore
    Expectation::macro($verb, function (BlastCloud\Chassis\Expectation $e, $uri) use ($verb) {
        return $e->withEndpoint($uri, strtoupper($verb));
    });
}

Expectation::macro('endpoint', function (BlastCloud\Chassis\Expectation $e, $url, $method) {
    return $e->withEndpoint($url, strtoupper($method));
});

Expectation::macro('withoutQuery', function (BlastCloud\Chassis\Expectation $e) {
    return $e->withQuery([], true);
});