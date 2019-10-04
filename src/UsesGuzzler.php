<?php

namespace BlastCloud\Guzzler;

trait UsesGuzzler
{
    /** @var Guzzler */
    public $guzzler;

    /**
     * @before
     */
    public function setUpGuzzler()
    {
        $engine = $this->engineName();

        $this->$engine = new Guzzler($this);
    }

    private function engineName()
    {
        return defined('self::ENGINE_NAME')
            ? self::ENGINE_NAME
            : 'guzzler';
    }

    /**
     * @after
     * Run through the list of expectations that were made and
     * evaluate all requests in the history. Closure::call()
     * is used to hide this method from the user APIs.
     */
    public function runGuzzlerAssertions()
    {
        $name = $this->engineName();
        (function () {
            $this->runExpectations();
        })->call($this->$name);
    }
}