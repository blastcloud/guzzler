<?php

namespace tests\Helpers;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
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

    public function testMacro()
    {
        Expectation::macro('inlineTest', function (Expectation $expect, $url) {
            return $expect->get($url);
        });

        $this->guzzler->queueMany(new Response(), 2);

        $url = '/somewhere';

        $this->guzzler->expects($this->once())
            ->inlineTest($url)
            // This second one comes from the testFiles/macros.php file loaded
            // with the phpunit.xml extension
            ->fromFile($url);

        $this->client->get($url);
        $this->client->post($url);
    }

    public function testAsynchronous()
    {
        $this->guzzler->queueMany(new Response(), 2);

        $this->guzzler->expects($this->once())
            ->asynchronous();

        // First test a passing call
        $this->client->getAsync('/woeiwj')->wait();

        // Now test a failing call
        $this->client->get('/somewhere');

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bsynchronous\b/");

        $this->guzzler->assertLast(function (Expectation $e) {
            return $e->asynchronous();
        });
    }

    public function testSynchronous()
    {
        $this->guzzler->queueMany(new Response(), 2);

        $this->guzzler->expects($this->once())
            ->synchronous();

        // First test a passing call
        $this->client->get('/a-url');

        // Now test a failing call
        $this->client->getAsync('/anywhere')->wait();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bsynchronous\b/");

        $this->guzzler->assertLast(function (Expectation $e) {
            return $e->synchronous();
        });
    }
}