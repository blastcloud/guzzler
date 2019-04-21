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

    public function setUp(): void
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

    public function testEachConvenienceVerbMethodDoesntErr()
    {
        $expectation = $this->guzzler->expects($this->never());

        foreach (Expectation::VERBS as $verb) {
            $expectation->$verb('/a-url');
        }
    }

    public function testOverrideInline()
    {
        Expectation::macro('original', function ($e) {
            return $e->will(new Response());
        });

        $this->assertEquals(0, $this->guzzler->queueCount());

        $this->guzzler->expects($this->never())
            ->original();

        $this->assertEquals(1, $this->guzzler->queueCount());

        Expectation::macro('original', function ($e) {
            return $e->will(new Response(), 5);
        });

        $this->guzzler->expects($this->never())
            ->original();

        $this->assertEquals(6, $this->guzzler->queueCount());
    }

    /**
     * @runInSeparateProcess
     */
    public function testOverrideProvidedMacro()
    {
        Expectation::macro('synchronous', function ($e) {
            return $e->will(new Response(), 10);
        });

        $this->guzzler->expects($this->never())
            ->synchronous();

        $this->assertEquals(10, $this->guzzler->queueCount());
    }
}