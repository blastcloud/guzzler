<?php

use PHPUnit\Framework\TestCase;

class ExpectationTest extends TestCase
{
    use \Guzzler\Guzzler;

    /** @var \GuzzleHttp\Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testExpectsReturnsExpectationInstanceAndIsChainable()
    {
        $result = $this->guzzler->expects($this->once())
            ->endpoint('/somewhere', 'GET');

        $this->assertInstanceOf(\Guzzler\Expectation::class, $result);
    }
}