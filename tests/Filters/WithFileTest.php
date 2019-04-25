<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
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

    public function testMultipartEliminatesNoFile()
    {
        $this->guzzler->expects($this->never())
            ->withFile('first', 'something')
            ->will(new Response());

        $this->client->post('/woeiw', [
            'multipart' => [
                [
                    'name' => 'fields',
                    'contents' => 'value'
                ]
            ]
        ]);
    }

    public function testWithoutMultipart()
    {
        $this->guzzler->expects($this->never())
            ->withFile('first', 'something')
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

        $this->client->post('/awoeiu', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($location, 'r'),
                    'filename' => 'spikity-spockity.txt'
                ],
                [
                    'name' => 'file2',
                    'contents' => new Stream(fopen($location, 'r'))
                ]
            ]
        ]);

        // Resource
        $this->guzzler->assertFirst(function ($e) use ($location) {
            return $e->withFile('file', fopen($location, 'r'))
                ->withFileName('spikity-spockity.txt')
                ->withFileMime('text/plain');
        });
    }
}