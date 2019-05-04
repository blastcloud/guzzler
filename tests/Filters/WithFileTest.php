<?php

namespace tests\Filters;

use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\Helpers\File;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class WithFileTest extends TestCase
{
    use UsesGuzzler;

    const TEXT_FILE = __DIR__.'/../testFiles/test-file.txt';
    const IMG_FILE = __DIR__.'/../testFiles/blast-cloud.jpg';

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testFileThrowsExceptionForNonExistentProperty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp("/\bsomething property does not exist\b/");

        $file = new File();
        $file->something = 'aoiweuowiue';
    }

    public function testMultipartEliminatesNoFile()
    {
        $this->guzzler->expects($this->never())
            ->withFile('first', File::create(['contents' => 'something']))
            ->will(new Response());

        $this->client->post('/woeiw', [
            'multipart' => [
                [
                    'name' => 'first',
                    'contents' => 'value'
                ]
            ]
        ]);
    }

    public function testWithoutMultipart()
    {
        $this->guzzler->expects($this->never())
            ->withFile('first', File::create(['contents' => 'something']))
            ->will(new Response());

        $this->client->post('/aoweiu', [
            'form_params' => [
                'first' => 'something'
            ]
        ]);
    }

    public function testWithFileUsingStringResourceAndFileLocation()
    {
        $this->guzzler->queueResponse(new Response());
        $filename = 'spikity-spockity.txt';

        $this->client->post('/awoeiu', [
            'multipart' => [
                [
                    'name' => 'file1',
                    'contents' => fopen(self::TEXT_FILE, 'r'),
                    'filename' => $filename
                ]
            ]
        ]);

        // File Location
        $this->guzzler->assertLast(function (Expectation $e) {
            return $e->withFiles([
                'file1' => File::create([
                    'contents' => fopen(self::TEXT_FILE, 'r')
                ])
            ]);
        });

        // Resource
        $this->guzzler->assertFirst(function (Expectation $e) use ($filename) {
            return $e->withFile('file1', File::create([
                        'contents' => fopen(self::TEXT_FILE, 'r'),
                        'filename' => $filename,
                        'contentType' => 'text/plain'
                    ])
                );
        });
    }

    public function testFilesWithImageFileAndManualFileFields()
    {
        $file = new File();
        $file->contents = fopen(self::IMG_FILE, 'r');

        $this->guzzler->expects($this->once())
            ->withFiles([
                'avatar' => $file
            ])->will(new Response(201));

        $this->client->post('/awoeiu', [
            'multipart' => [
                [
                    'name' => 'avatar',
                    'contents' => fopen(self::IMG_FILE, 'r')
                ]
            ]
        ]);
    }

    public function testFileExclusive()
    {
        $this->guzzler->queueMany(new Response(), 2);

        $this->client->post('/aoiwoiu', [
            'multipart' => [
                [
                    'name' => 'text',
                    'contents' => fopen(self::TEXT_FILE, 'r')
                ],
                [
                    'name' => 'avatar',
                    'contents' => fopen(self::IMG_FILE, 'r')
                ]
            ]
        ]);

        $this->guzzler->assertNotFirst(function (Expectation $e) {
            return $e->withFiles([
                'text' => File::create([
                    'name' => 'text',
                    'contents' => fopen(self::TEXT_FILE, 'r')
                ])
            ], true);
        });

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withFiles([
                'text' => File::create([
                    'name' => 'text',
                    'contents' => fopen(self::TEXT_FILE, 'r')
                ])
            ]);
        });
    }

    public function testHeaderComparing()
    {
        $this->guzzler->queueResponse(new Response());

        $this->client->post('/aoeiu', [
            'multipart' => [
                [
                    'name' => 'something',
                    'contents' => 'aowieuw',
                    'filename' => 'overset',
                    'headers' => [
                        'Foo' => 'Baz'
                    ]
                ]
            ]
        ]);

        $this->guzzler->assertFirst(function (Expectation $e) {
            return $e->withFile('something', File::create([
                'headers' => ['Foo' => 'Baz']
            ]));
        });
    }
}