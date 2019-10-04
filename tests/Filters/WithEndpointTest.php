<?php

namespace tests\Filters;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class WithEndpointTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithEndpointFails()
    {
        $this->guzzler->queueResponse(new Response());

        $this->client->get('/url');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp('/\bPOST\b/');

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withEndpoint('/url', 'POST');
        });
    }
}