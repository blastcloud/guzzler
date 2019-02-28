<?php

namespace tests;

use PHPUnit\Framework\TestCase;

class UsesGuzzlerTest extends TestCase
{
    public function testBeforeAndAfterAnnotations()
    {
        $test = new \PHPUnit\Framework\TestSuite(GuzzlerAnnotationsTest::class);
        GuzzlerAnnotationsTest::$afterWasRun = null;

        $test->run();

        $this->assertEquals('after has run', GuzzlerAnnotationsTest::$afterWasRun);
    }

}