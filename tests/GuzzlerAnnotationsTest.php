<?php

namespace tests;

class GuzzlerAnnotationsTest extends \PHPUnit\Framework\TestCase
{
    use \Guzzler\UsesGuzzler;

    public static $afterWasRun;

    public function testWrapperIsSetupBeforeTest(): void
    {
        $this->assertObjectHasAttribute('guzzler', $this);
        $this->assertInstanceOf(\Guzzler\Guzzler::class, $this->guzzler);
    }

    public function testExpectationsAreRunAfter()
    {
        $this->guzzler = new Class {
            public function runExpectations()
            {
                GuzzlerAnnotationsTest::$afterWasRun = 'after has run';
            }
        };

        // This is here just so the test isn't marked 'risky' and skipped.
        $this->addToAssertionCount(1);
    }
}