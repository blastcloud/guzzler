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
}