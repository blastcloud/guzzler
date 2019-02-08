<?php

namespace Guzzler;

trait Guzzler
{
    /** @var Wrapper */
    public $guzzler;

    /**
     * @before
     */
    public function setUpGuzzler()
    {
        $this->guzzler = new Wrapper($this);
    }

    /**
     * @after
     * Run through the list of expects() that
     * were made and make run assertions on
     * the history.
     */
    public function runGuzzlerAssertions()
    {
        $this->guzzler->runExpectations();
    }
}