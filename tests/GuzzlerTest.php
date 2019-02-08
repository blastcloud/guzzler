<?php

use PHPUnit\Framework\TestCase;
use Guzzler\Guzzler;

class GuzzlerTest extends TestCase {
    use Guzzler;

    public function testBeforeAndAfterAnnotations()
    {
        $test = new \PHPUnit\Framework\TestSuite(GuzzlerAnnotationsTest::class);
        GuzzlerAnnotationsTest::$afterWasRun = null;

        $test->run();

        $this->assertEquals('after has run', GuzzlerAnnotationsTest::$afterWasRun);
    }
}