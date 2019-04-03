<?php

namespace tests\Helpers;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class MacrosTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testMacAddedInline()
    {
        Expectation::macro('inlineTest', function (Expectation $expect, $url) {
            return $expect->get($url);
        });

        $this->guzzler->queueResponse(new Response());

        $url = '/somewhere';

        $this->guzzler->expects($this->once())
            ->inlineTest($url);

        $this->client->get($url);
    }
}