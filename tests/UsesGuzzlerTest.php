<?php

namespace Tests;

use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class UsesGuzzlerTest extends TestCase
{
    use UsesGuzzler;

    const ENGINE_NAME = 'renamed';

    public function testBeforeAndAfterAnnotations()
    {
        if (!method_exists(TestSuite::class, 'fromClassName')) {
            $this->markTestSkipped();
        }

        $test = TestSuite::fromClassName(GuzzlerAnnotationsTest::class);
        GuzzlerAnnotationsTest::$afterWasRun = null;

        $test->run();

        $this->assertEquals('after has run', GuzzlerAnnotationsTest::$afterWasRun);
    }
}