<?php

namespace Guzzler;

use GuzzleHttp\Psr7\Uri;

trait Filters
{
    /** @var Uri */
    protected $endpoint;

    /** @var string */
    protected $method;

    protected $query = [];
    protected $queryExclusive = false;

    protected $headers = [];

    protected $options = [];

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
        return array_filter($history, function ($call) {
            return $call['request']->getMethod() == $this->method
                && $call['request']->getUri()->getPath() == $this->endpoint->getPath();
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

    /**
     * Narrow to only those history items with the specific body.
     *
     * @param array $history
     * @return array
     */
    protected function filterByBody(array $history) {
        return array_filter($history, function ($call) {
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
        return array_filter($history, function ($call) {
            return $call['request']->getProtocolVersion() == $this->protocol;
        });
    }

    /**
     * Narrow to only those history items with the specified options.
     *
     * @param array $history
     * @return array
     */
    protected function filterByOptions(array $history)
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

    /**
     * Narrow to only those history requests with the specified query params. By default
     * more can exist, but if the "exclusive" flag was true, we eliminate if more
     * query params exist. Also, order of query params is not considered.
     *
     * @param array $history
     * @return array
     */
    protected function filterByQuery(array $history)
    {
        return array_filter($history, function ($call) {
            parse_str($call['request']->getUri()->getQuery(), $query);

            foreach ($this->query as $key => $value) {
                if (
                    !isset($query[$key])
                    || (is_array($query[$key])
                            && !in_array($value, $query[$key])
                        )
                    || $query[$key] != $value
                ) {
                    return false;
                }
            }

            // Only if "exclusive" flag is set to true.
            if ($this->queryExclusive && count($query) > count($this->query)) {
                return false;
            }

            return true;
        });
    }
}