<?php

namespace tests;

use BlastCloud\Guzzler\UsesGuzzler;

class GuzzlerAnnotationsTest extends \PHPUnit\Framework\TestCase
{
    use UsesGuzzler;

    public static $afterWasRun;

    public function testWrapperIsSetupBeforeTest(): void
    {
        $this->assertObjectHasAttribute('guzzler', $this);
        $this->assertInstanceOf(\BlastCloud\Guzzler\Guzzler::class, $this->guzzler);
    }

    public function testExpectationsAreRunAfter()
    {
        $this->guzzler = new Class
        {
            public function runExpectations()
            {
                GuzzlerAnnotationsTest::$afterWasRun = 'after has run';
            }
        };

        // This is here just so the test isn't marked 'risky' and skipped.
        $this->addToAssertionCount(1);
    }
}