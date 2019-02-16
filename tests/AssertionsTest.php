<?php

namespace tests;

use GuzzleHttp\Client;
use Guzzler\Expectation;
use Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AssertionsTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function catchAnything($callable)
    {
        try {
            $callable();
            return null;
        } catch (\Exception | \Error $e) {
            return $e;
        }
    }

    public function testAssertNoHistoryPasses()
    {
        $this->guzzler->assertNoHistory();
    }

    public function testAssertNoHistoryFails()
    {
        $this->guzzler->queueResponse(new Response(200));
        $this->client->get('woeij');

        $res = $this->catchAnything(function () {
            $this->guzzler->assertNoHistory();
        });

        $this->assertNotNull($res);
        $this->assertNotNull($res->getMessage());

        // With message provided.
        $message = 'some special message';
        $resWithMessage = $this->catchAnything(function () use ($message) {
            $this->guzzler->assertNoHistory($message);
        });

        $this->assertEquals($message, $resWithMessage->getMessage());
    }

    public function testAssertHistoryCountPasses()
    {
        $this->guzzler->queueResponse(new Response(), new Response());

        $this->client->get('woiej');
        $this->client->post('aowei');

        $this->guzzler->assertHistoryCount(2);
    }

    public function testAssertHistoryCountFails()
    {
        $res = $this->catchAnything(function () {
            $this->guzzler->assertHistoryCount(6);
        });

        $this->assertNotNull($res->getMessage());

        $message = 'my message';
        $res = $this->catchAnything(function () use ($message) {
            $this->guzzler->assertHistoryCount(3, $message);
        });

        $this->assertEquals($message, $res->getMessage());
    }

    public function testEachAssertRequiringHistoryFailsOnEmptyHistory()
    {
        foreach (['assertFirst', 'assertLast', 'assertAll'] as $method) {
            $r = $this->catchAnything(function () use ($method) {
                $this->guzzler->$method(function ($expectation) {});
            });

            $this->assertStringContainsString('empty', $r->getMessage());
        }
    }

    public function testAssertFirstPasses()
    {
        $this->guzzler->queueResponse(new Response(), new Response());
        $this->client->get('/a-url');
        $this->client->post('/nowhere');

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->get('/a-url');
        });
    }

    public function testAssertFirstFails()
    {
        $this->guzzler->queueResponse(new Response(), new Response());
        $this->client->get('/a-url');
        $this->client->post('/nowhere');

        $r = $this->catchAnything(function () {
            $this->guzzler->assertLast(function ($e) {
                return $e->post('/a-url');
            });
        });

        $this->assertNotNull($r);

        // Custom Message
        $m = 'the special message';
        $r = $this->catchAnything(function () use ($m) {
            $this->guzzler->assertLast(function ($e) {
               return $e->post('/a-url');
            }, $m);
        });

        $this->assertEquals($m, $r->getMessage());
    }

    public function setUpAssertAll()
    {
        $this->guzzler->queueResponse(
            new Response(),
            new Response(),
            new Response()
        );

        $key = 'Authorization'; $value = 'abdecfg';
        $options = ['headers' => [$key => $value]];

        $this->client->get('woeij', $options);
        $this->client->get('aeice', $options);

        return $options;
    }

    public function testAssertAllSuccess()
    {
        $options = $this->setUpAssertAll();
        $this->client->post('ceowu', $options);

        $this->guzzler->assertAll(function ($e) use ($options) {
            return $e->withHeaders($options['headers']);
        });
    }

    public function testAssertAllFail()
    {
        $options = $this->setUpAssertAll();
        $this->client->get('aeice');

        $r = $this->catchAnything(function () use ($options) {
            $this->guzzler->assertAll(function ($e) use ($options) {
                return $e->withHeaders($options['headers']);
            });
        });

        $this->assertNotNull($r);

        // With custom message
        $message = 'aoiucoewuewknoih';
        $p = $this->catchAnything(function () use ($options, $message) {
            $this->guzzler->assertAll(function ($e) use ($options, $message) {
                return $e->withHeaders($options['headers']);
            }, $message);
        });

        $this->assertEquals($message, $p->getMessage());
    }
}