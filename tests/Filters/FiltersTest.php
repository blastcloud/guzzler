<?php

namespace tests\Filters;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use tests\testFiles\WithTest;

class FiltersTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testAddNamespace()
    {
        Expectation::addNamespace('tests\\testFiles');

        $this->guzzler->expects($this->once())
            ->withTest('something', 'another')
            ->will(new Response());

        $this->client->get('/anything');

        $this->assertEquals('something', WithTest::$first);
        $this->assertEquals('another', WithTest::$second);
    }
}