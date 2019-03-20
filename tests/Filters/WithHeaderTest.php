<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class WithHeaderTest extends TestCase
{
    use UsesGuzzler;

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
}