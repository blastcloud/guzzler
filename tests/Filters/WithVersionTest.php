<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use tests\ExceptionMessageRegex;

class WithVersionTest extends TestCase
{
    use UsesGuzzler, ExceptionMessageRegex;

    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithProtocol()
    {
        $this->guzzler->expects($this->once())
            ->withVersion(2.0);

        $this->guzzler->queueResponse(new Response(200));

        $this->client->get('/aoweij', [
            'version' => 2.0
        ]);
    }

    public function testWithBodyError()
    {
        $this->expectException(AssertionFailedError::class);
        $this->{self::$regexMethodName}("/\bVersion:\b/");
        $this->{self::$regexMethodName}("/\b2\b/");

        $this->guzzler->queueResponse(new Response());
        $this->client->get('/aowei');

        $this->guzzler->assertFirst(function ($e) {
            return $e->withVersion(2.0);
        });
    }
}