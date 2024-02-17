<?php

namespace Tests\Filters;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use tests\ExceptionMessageRegex;

class WithHeaderTest extends TestCase
{
    use UsesGuzzler, ExceptionMessageRegex;

    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithHeaders()
    {
        $headers = [
            'X-Something' => 'Special',
            'host' => 'example.com'
        ];

        $this->guzzler->queueResponse(new Response(200));

        $this->guzzler->expects($this->once())
            ->endpoint('/url', 'GET')
            ->withHeader('Auth', 'Fantastic')
            ->withHeaders($headers);

        $this->client->get('/url', [
            'headers' => $headers + ['Auth' => 'Fantastic']
        ]);
    }

    public function testWithHeadersFail()
    {
        $this->guzzler->queueResponse(new Response(200));

        $this->expectException(AssertionFailedError::class);
        $this->{self::$regexMethodName}('/\bHeaders\b/');

        $this->client->get('/aowieu');

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withHeader('X-Special', 'the value');
        });
    }
}