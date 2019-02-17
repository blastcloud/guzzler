<?php

namespace tests;

use GuzzleHttp\Client;
use Guzzler\Expectation;
use Guzzler\UndefinedIndexException;
use Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AssertionsTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public $options = [
        'headers' => ['Guzzler' => '**the-values**']
    ];

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

    public function setUpHistory()
    {
        $count = count(Expectation::VERBS);
        $this->guzzler->queueMany(new Response(), $count + 4);

        foreach (Expectation::VERBS as $verb) {
            $this->client->$verb('/a-url', $this->options);
        }

        for ($i = 0; $i < 4; $i++) {
            $this->client->get('/a-different-url', $this->options);
        }

        return $count + 4;
    }

    public function testAssertNoHistoryPasses()
    {
        $this->guzzler->assertNoHistory();
    }

    public function testAssertNoHistoryFails()
    {
        $this->setUpHistory();

        $res = $this->catchAnything(function () {
            $this->guzzler->assertNoHistory();
        });

        $this->assertNotNull($res);

        // With message provided.
        $message = 'some special message';
        $resWithMessage = $this->catchAnything(function () use ($message) {
            $this->guzzler->assertNoHistory($message);
        });

        $this->assertEquals($message, $resWithMessage->getMessage());
    }

    public function testAssertHistoryCountPasses()
    {
        $this->guzzler->assertHistoryCount(0);

        $this->guzzler->assertHistoryCount($this->setUpHistory());
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

    public function testAssertFirstPasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->get('/a-url');
        });
    }

    public function testAssertFirstFails()
    {
        $this->setUpHistory();

        $r = $this->catchAnything(function () {
            $this->guzzler->assertFirst(function ($e) {
                return $e->post('/a-url');
            });
        });

        $this->assertNotNull($r);

        // Custom Message
        $m = 'the special message';
        $r = $this->catchAnything(function () use ($m) {
            $this->guzzler->assertFirst(function ($e) {
               return $e->post('/a-url');
            }, $m);
        });

        $this->assertEquals($m, $r->getMessage());
    }

    public function testAssertAllSuccess()
    {
        $this->setUpHistory();

        $this->guzzler->assertAll(function ($e) {
            return $e->withHeaders($this->options['headers']);
        });
    }

    public function testAssertAllEmpty()
    {
        $this->expectException(UndefinedIndexException::class);
        $this->guzzler->assertAll(function ($e) {});
    }

    public function testAssertAllFail()
    {
        $this->setUpHistory();

        $r = $this->catchAnything(function () {
            $this->guzzler->assertAll(function ($e) {
                return $e->get('/a-url');
            });
        });

        $this->assertNotNull($r);

        // With custom message
        $message = 'aoiucoewuewknoih';
        $p = $this->catchAnything(function () use ($message) {
            $this->guzzler->assertAll(function ($e) {
                return $e->get('/a-url');
            }, $message);
        });

        $this->assertEquals($message, $p->getMessage());
    }

    public function testAssertIndexUndefined()
    {
        $this->expectException(UndefinedIndexException::class);
        $this->guzzler->assertIndexes([7], function ($e){});
    }

    public function testAssertLastPasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertLast(function ($e) {
            return $e->get('/a-different-url');
        });
    }
}