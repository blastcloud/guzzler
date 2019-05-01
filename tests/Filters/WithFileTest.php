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
        $this->guzzler->queueResponse(new Response());
        /*$this->guzzler->expects($this->never())
            ->withFile('first', File::create(['contents' => 'something']))
            ->will(new Response());*/

        $this->client->post('/woeiw', [
            'multipart' => [
                [
                    'name' => 'first',
                    'contents' => 'value'
                ]
            ]
        ]);

        $this->guzzler->assertNone(function ($e) {
           return $e->withFile('first', File::create(['contents' => 'something']));
        });
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
        $location = realpath(__DIR__.'/../testFiles/test-file.txt');

        $filename = 'spikity-spockity.txt';

        $this->client->post('/awoeiu', [
            'multipart' => [
                [
                    'name' => 'file1',
                    'contents' => fopen($location, 'r'),
                    'filename' => $filename
                ]
            ]
        ]);

        // File Location
        $this->guzzler->assertLast(function (Expectation $e) use ($location) {
            return $e->withFiles([
                'file1' => File::create(['contents' => $location])
            ]);
        });

        //die(var_dump($this->guzzler->getHistory(0, 'request')->getBody()->getContents()));

        // Resource
        $this->guzzler->assertFirst(function (Expectation $e) use ($location, $filename) {
            return $e->withFile('file1', File::create([
                        'contents' => fopen($location, 'r'),
                        'filename' => $filename,
                        'contentType' => 'text/plain'
                    ])
                );
        });
    }
}