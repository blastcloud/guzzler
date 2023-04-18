<?php

namespace tests;

use BlastCloud\Guzzler\UsesGuzzler;
use BlastCloud\Guzzler\Guzzler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class UsesGuzzlerTest extends TestCase
{
    use UsesGuzzler;

    const ENGINE_NAME = 'renamed';

    public function testBeforeAndAfterAnnotations()
    {
        $test = TestSuite::fromClassName(GuzzlerAnnotationsTest::class);
        GuzzlerAnnotationsTest::$afterWasRun = null;

        $test->run();

        $this->assertEquals('after has run', GuzzlerAnnotationsTest::$afterWasRun);
    }
}