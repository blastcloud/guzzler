<?php

namespace BlastCloud\Guzzler;

use GuzzleHttp\Psr7\Uri;

trait Filters
{
    /** @var string */
    protected $body;

    /** @var Uri */
    protected $endpoint;

    protected $form = [];
    protected $formExclusive = false;

    protected $headers = [];

    protected $json = [];
    protected $jsonExclusion = false;

    /** @var string */
    protected $method;

    protected $messages = [];

    protected $options = [];

    /** @var string */
    protected $protocol;

    protected $query = [];
    protected $queryExclusive = false;

    /**
     * Narrow to only those history items with the matching endpoint.
     *
     * @param array $history
     * @return array
     */
    protected function filterByEndpoint(array $history)
    {
        $this->messages[] = str_pad('Method:', self::STR_PAD).$this->method;

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
        $this->messages[] = str_pad('Headers:', self::STR_PAD)
            .json_encode($this->headers, JSON_PRETTY_PRINT);

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
    protected function filterByBody(array $history)
    {
        $this->messages[] = str_pad('Body:', self::STR_PAD).$this->body;

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
    protected function filterByProtocol(array $history)
    {
        $this->messages[] = str_pad('Protocol:', self::STR_PAD).$this->protocol;

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
        $this->messages[] = str_pad('Options:', self::STR_PAD)
            .json_encode($this->options, JSON_PRETTY_PRINT);

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
        $e = $this->queryExclusive ? 'true' : 'false';
        $this->messages[] = "Query: (Exclusive: {$e})".json_encode($this->query);

        return array_filter($history, function ($call) {
            parse_str($call['request']->getUri()->getQuery(), $parsed);
            return $this->testFields($this->query, $parsed, $this->queryExclusive);
        });
    }

    protected function testFields(array $fields, array $parsed, $exclusive = false)
    {
        foreach ($fields as $key => $value) {
            if (
                !isset($parsed[$key])
                || (is_array($parsed[$key]) && !in_array($value, $parsed[$key]))
                || $parsed[$key] != $value
            ) {
                return false;
            }
        }

        // Only if "exclusive" flag is set to true.
        if ($exclusive && count($parsed) > count($fields)) {
            return false;
        }

        return true;
    }

    /**
     * Narrow to only those history requests with the specified form params. By default
     * more can exist, but if the "exclusive" flag was true, we eliminate if more
     * form params exist. Also, order of form params is not considered.
     *
     * @param array $history
     * @return array
     */
    protected function filterByForm(array $history)
    {
        $e = $this->formExclusive ? 'true' : 'false';
        $this->messages[] = "Form: (Exclusive: {$e}) "
            .json_encode($this->form, JSON_PRETTY_PRINT);

        return array_filter($history, function ($call) {
            parse_str($call['request']->getBody(), $parsed);
            return $this->testFields($this->form, $parsed, $this->formExclusive);
        });
    }

    /**
     * Narrow to only those history requests with the specified JSON body.
     *
     * @param array $history
     * @return array
     */
    protected function filterByJson(array $history)
    {
        $e = $this->jsonExclusion ? 'true' : 'false';
        $this->messages[] = "JSON: (Exclusive: {$e}) "
            .json_encode($this->json, JSON_PRETTY_PRINT);

        return array_filter($history, function ($call) {
            $body = json_decode($call['request']->getBody(), true);
            return $body == $this->json;
        });
    }
}