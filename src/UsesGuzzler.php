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
        $this->guzzler = new Guzzler($this);
    }

    /**
     * @after
     * Run through the list of expectations that were made and
     * evaluate all requests in the history. Closure::call()
     * is used to hide this method from the user APIs.
     */
    public function runGuzzlerAssertions()
    {
        (function () {
            $this->runExpectations();
        })->call($this->guzzler);
    }
}