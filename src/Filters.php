<?php

namespace Guzzler;

trait Filters
{
    /** @var string */
    protected $endpoint;

    /** @var string */
    protected $method;

    protected $headers = [];

    /** @var string */
    protected $body;

    /** @var string */
    protected $protocol;

    /**
     * Narrow to only those history items with the matching endpoint.
     *
     * @param array $history
     * @return array
     */
    protected function filterByEndpoint(array $history)
    {
        return array_filter($history, function($call) {
            return $call['request']->getMethod() == $this->method
                && $call['request']->getUri() == $this->endpoint;
        });
    }

    /**
     * Narrow to only those history items with give header values.
     *
     * @param array $history
     * @return array
     */
    protected function filterByHeaders(array $history)
    {
        return array_filter($history, function($call) {
            foreach ($this->headers as $key => $value) {
                $header = $call['request']->getHeader($key);

                if ($header != $value && !in_array($value, $header)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Narrow to only those history items with the specific body.
     *
     * @param array $history
     * @return array
     */
    protected function filterByBody(array $history) {
        return array_filter($history, function($call) {
            return $call['request']->getBody() == $this->body;
        });
    }

    /**
     * Narrow to only those history items with the specific protocol.
     *
     * @param array $history
     * @return array
     */
    protected function filterByProtocol(array $history) {
        return array_filter($history, function($call) {
            return $call['request']->getProtocolVersion() == $this->protocol;
        });
    }
}