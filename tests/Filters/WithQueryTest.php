<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use BlastCloud\Guzzler\Expectation;
use PHPUnit\Framework\AssertionFailedError;

class WithQueryTest extends TestCase
{
    use UsesGuzzler;

    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithQuery()
    {
        $this->guzzler->queueMany(new Response(), 3);

        $this->guzzler->expects($this->once())
            ->withQuery([
                'second' => 'another-value'
            ]);

        $this->client->get('/some-url', [
            'query' => ['first' => 'a-value', 'second' => 'another-value']
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bExclusive: true\b/");

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withQuery(['second' => 'another-value'], true);
        });
    }

    public function testWithQueryKey()
    {
        $this->guzzler->queueResponse(new Response());

        $this->guzzler->expects($this->once())
            ->withQueryKey('a-special-key');

        $this->client->get('/some-url?something=a-value&a-special-key');

        $this->expectException(AssertionFailedError::class);
        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withQueryKey('a-different-key');
        });
    }

    public function testWithQueryKeys()
    {
        $this->guzzler->queueResponse(new Response());

        $this->guzzler->expects($this->once())
            ->withQueryKeys(['first', 'second']);

        $this->client->get('/some-url', [
            'query' => ['second' => 'values', 'first' => 'others']
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withQueryKeys(['third']);
        });
    }
}