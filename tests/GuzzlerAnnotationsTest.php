<?php

namespace Tests;

use BlastCloud\Guzzler\Guzzler;
use BlastCloud\Guzzler\UsesGuzzler;

class GuzzlerAnnotationsTest extends \PHPUnit\Framework\TestCase
{
    use UsesGuzzler;

    public static ?string $afterWasRun;

    public function testWrapperIsSetupBeforeTest(): void
    {
        $this->assertTrue(property_exists($this, 'guzzler'));
        $this->assertInstanceOf(\BlastCloud\Guzzler\Guzzler::class, $this->guzzler);
    }

    public function testExpectationsAreRunAfter()
    {
        $this->guzzler = new Class($this) extends Guzzler
        {
            public function runExpectations(): void
            {
                GuzzlerAnnotationsTest::$afterWasRun = 'after has run';
            }
        };

        // This is here just so the test isn't marked 'risky' and skipped.
        $this->addToAssertionCount(1);
    }
}