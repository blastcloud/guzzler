<?php

namespace tests\Filters;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class WithFileTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithFileEliminatesNoFile()
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

    public function testWithFileUsingStringResourceAndFileLocation()
    {
        $this->guzzler->queueResponse(new Response());
        $location = realpath(__DIR__.'/../testFiles/test-file.txt');

        $this->client->post('/awoeiu', [
            [
                'name' => 'file',
                'contents' => fopen($location, 'r')
            ]
        ]);

        // Resource
        $this->guzzler->assertFirst(function ($e) use ($location) {
            return $e->withFile('file', fopen($location, 'r'));
        });

        // Contents String
        $this->guzzler->assertFirst(function ($e) use ($location) {
            return $e->withFile('file', file_get_contents($location));
        });

        // File Location
        $this->guzzer->assertFirst(function ($e) use ($location) {
            return $e->withFile('file', $location);
        });
    }
}