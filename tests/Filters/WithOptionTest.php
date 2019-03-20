<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use BlastCloud\Guzzler\Expectation;

class WithOptionTest extends TestCase
{
    use UsesGuzzler;

    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithOptions()
    {
        $this->guzzler->queueMany(new Response(), 2);

        $this->client->get('/woewij', ['stream' => true]);

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withOption('stream', true);
        });

        $options = ['verify' => false, 'allow_redirects' => false];
        $this->guzzler->expects($this->once())
            ->withOptions($options);

        $this->client->get('/woei', $options);
    }

    public function testWithOptionError()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bOptions\b/");

        $this->guzzler->queueResponse(new Response());
        $this->client->get('/aowei');

        $this->guzzler->assertFirst(function ($e) {
            return $e->withOptions(['something' => 'not']);
        });
    }
}