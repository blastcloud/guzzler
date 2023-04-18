<?php

namespace BlastCloud\Guzzler;

trait UsesGuzzler
{
    public Guzzler $guzzler;

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