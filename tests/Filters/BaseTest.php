<?php

namespace tests\Filters;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    use UsesGuzzler;

    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testAddThrowsErrorWhenMethodNotFound()
    {
        $this->expectException(\Error::class);
        $class = Expectation::class;
        $this->expectExceptionMessage("Call to undefined method {$class}::withBodyDouble()");

        $this->guzzler->expects($this->never())
            ->withBodyDouble('anything');
    }
}