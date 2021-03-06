<?php

namespace tests;

use BlastCloud\Guzzler\UsesGuzzler;
use BlastCloud\Guzzler\Guzzler;
use PHPUnit\Framework\TestCase;

class UsesGuzzlerTest extends TestCase
{
    use UsesGuzzler;

    const ENGINE_NAME = 'renamed';

    public function testBeforeAndAfterAnnotations()
    {
        $test = new \PHPUnit\Framework\TestSuite(GuzzlerAnnotationsTest::class);
        GuzzlerAnnotationsTest::$afterWasRun = null;

        $test->run();

        $this->assertEquals('after has run', GuzzlerAnnotationsTest::$afterWasRun);
    }

    public function testRenameEngine()
    {
        $this->assertInstanceOf(Guzzler::class, $this->renamed);
    }
}