<?php

namespace tests\Filters;


use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

class WithJsonTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testJson()
    {
        $this->guzzler->queueMany(new Response(), 3);

        $form = [
            'first' => 'a value',
            'second' => 'another value'
        ];

        $this->guzzler->expects($this->atLeastOnce())
            ->withJson($form);

        $this->client->post('/woeij', [
            'json' => $form
        ]);

        $nestedJson = [
            'first' => [
                'nested' => 'nested value'
            ]
        ];
        $this->guzzler->expects($this->once())
            ->withJson($nestedJson);

        $this->client->post('/coewiu', [
            'json' => $nestedJson
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bJSON\b/");

        $this->client->post('/awoei', [
            'json' => $form + ['woeij' => 'aoiejw']
        ]);

        $this->guzzler->assertLast(function ($expect) use ($form) {
            return $expect->withJson($form, true);
        });
    }
}