<?php

namespace tests;

use GuzzleHttp\Client;
use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\UndefinedIndexException;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;
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

    public function testAssertNoHistoryFailsDefaultMessage()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bno history\b/");

        $this->guzzler->assertNoHistory();
    }

    public function testAssertNoHistoryFailsWithCustomMessage()
    {
        $this->setUpHistory();

        $message = 'some special message';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->guzzler->assertNoHistory($message);
    }

    public function testAssertHistoryCountPasses()
    {
        $this->guzzler->assertHistoryCount(0);

        $this->guzzler->assertHistoryCount($this->setUpHistory());
    }

    public function testAssertHistoryCountFailsDefaultMessageOneRequest()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\b1 request\b/");

        $this->guzzler->assertHistoryCount(1);
    }

    public function testAssertHistoryCountFailsDefaultMessageMultipleRequests()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\b0 requests\b/");

        $this->setUpHistory();
        $this->guzzler->assertHistoryCount(0);
    }

    public function testAssertHistoryCountFailsWithCustomMessage()
    {
        $message = 'my message';

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\b{$message}\b/");

        $this->guzzler->assertHistoryCount(3, $message);
    }

    public function testAssertFirstPasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->get('/a-url');
        });
    }

    public function testAssertNotFirstPasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertNotFirst(function (Expectation $e) {
            return $e->post('/a-url');
        });
    }

    public function testAssertFirstFails()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bfirst\b/");

        $this->guzzler->assertFirst(function ($e) {
            return $e->post('/a-url');
        });
    }

    public function testAssertNotFirstFails()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bfirst\b/");
        $this->expectExceptionMessageRegExp("/\bnot meet\b/");

        $this->guzzler->assertNotFirst(function ($e) {
            return $e->get('/a-url');
        });
    }

    public function testAssertFirstFailsWithCustomMessage()
    {
        $this->setUpHistory();

        $m = 'the special message';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($m);

        $this->guzzler->assertFirst(function ($e) {
            return $e->post('/a-url');
        }, $m);
    }

    public function testAssertNotFirstFailsWithCustomMessage()
    {
        $this->setUpHistory();

        $m = 'A custom message';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($m);

        $this->guzzler->assertNotFirst(function ($e) {
            return $e->get('/a-url');
        }, $m);
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
        $this->expectExceptionMessageRegExp("/\bempty\b/");
        $this->guzzler->assertAll(function ($e) {
        });
    }

    public function testAssertAllFailDefaultMessage()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        // Should include indexes of failed history items
        $this->expectExceptionMessageRegExp("/\b[1,2,3,4,5,6]\b/");
        $this->guzzler->assertAll(function ($e) {
            return $e->get('/a-url');
        });
    }

    public function testAssertAllFailWithCustomMessage()
    {
        $this->setUpHistory();

        $message = 'aoiucoewuewknoih';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->guzzler->assertAll(function ($e) {
            return $e->get('/a-url');
        }, $message);
    }

    public function testAssertIndexUndefined()
    {
        $this->expectException(UndefinedIndexException::class);
        // Should include the index number of failure
        $this->expectExceptionMessageRegExp("/\b[7]\b/");
        $this->guzzler->assertIndexes([7], function ($e) {
        });
    }

    public function testAssertLastPasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertLast(function ($e) {
            return $e->get('/a-different-url');
        });
    }

    public function testAssertNotLastPasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertNotLast(function ($e) {
            return $e->post('/a-different-url');
        });
    }

    public function testAssertLastFailsDefaultMessage()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\blast request\b/");

        $this->guzzler->assertLast(function ($e) {
            return $e->post('/aowiej');
        });
    }

    public function testAssertNotLastFailsWithDefaultMessage()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bdid not\b/");

        $this->guzzler->assertNotLast(function ($e) {
            return $e->get('/a-different-url');
        });
    }

    public function testAssertLastFailsWithCustomMessage()
    {
        $this->setUpHistory();

        $message = 'aoweijcemhoiwe';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->guzzler->assertLast(function ($e) {
            return $e->options('/aoweij');
        }, $message);
    }

    public function testAssertNotLastFailsWithCustomMessage()
    {
        $this->setUpHistory();

        $m = 'Lorem ipsum sal it amet.';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($m);

        $this->guzzler->assertNotLast(function ($e) {
            return $e->get('/a-different-url');
        }, $m);
    }

    public function testAssertNonePasses()
    {
        $this->setUpHistory();

        $this->guzzler->assertNone(function ($e) {
            return $e->withOption('verify', false);
        });
    }

    public function testAssertNoneFailsDefaultMessage()
    {
        $this->setUpHistory();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\b[3]\b/");

        $this->guzzler->assertNone(function ($e) {
            return $e->delete('/a-url');
        });
    }

    public function testAssertNoneFailsWithCustomMessage()
    {
        $this->setUpHistory();

        $message = 'The hills are alive with the sound of music.';
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->guzzler->assertNone(function ($e) {
            return $e->delete('/a-url');
        }, $message);
    }
}