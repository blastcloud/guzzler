<?php

namespace tests\Filters;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\AssertionFailedError;

class WithFormTest extends TestCase
{
    use UsesGuzzler;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testFormParamsContains()
    {
        $this->guzzler->queueResponse(new Response());

        $form = [
            'first' => 'a value',
            'second' => 'another value'
        ];

        $this->guzzler->expects($this->once())
            ->withFormField('first', 'a value');

        $this->client->post('/the-form', [
            'form_params' => $form
        ]);

        $this->guzzler->assertFirst(function ($expect) use ($form) {
            return $expect->withForm($form);
        });

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bForm\b/");

        $this->guzzler->assertLast(function ($expect) {
            return $expect->withFormField('doesntexist', 'Some value');
        });
    }

    public function testFormParamsExclusive()
    {
        $this->guzzler->queueMany(new Response(), 2);

        $form = [
            'first' => 'value',
            'second' => 'something else'
        ];

        $this->client->post('/aoiwoiu', [
            'form_params' => $form + ['third' => 'different']
        ]);

        $this->client->post('/caowei', ['form_params' => $form]);

        $this->guzzler->assertNotFirst(function ($e) use ($form) {
            return $e->withForm($form, true);
        });
        $this->guzzler->assertLast(function ($e) use ($form) {
            return $e->withForm($form, true);
        });
    }

    public function testWithFormMultipart()
    {
        $this->guzzler->queueResponse(new Response());
        $this->client->post('/aoweiu', [
            'multipart' => [
                [
                    'name' => 'first',
                    'contents' => 'value'
                ],
                [
                    'name' => 'second',
                    'contents' => 'another',
                    'headers' => ['X-Baz' => 'best']
                ],
                [
                    'name' => 'test-file',
                    'contents' => fopen(__DIR__ . '/../testFiles/test-file.txt', 'r'),
                    'filename' => 'rewrite-name.txt',
                    'headers' => ['Heads' => 'up']
                ],
                [
                    'name' => 'test-image',
                    'contents' => fopen(__DIR__.'/../../Guzzler-logo.svg', 'r'),
                    'filename' => 'overwrite.svg'
                ]
            ]
        ]);

        $this->guzzler->assertFirst(function ($e) {
            return $e->withForm([
                'second' => 'another',
                'first' => 'value'
            ]);
        });

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessageRegExp("/\bForm\b/");

        $this->guzzler->assertFirst(function ($e) {
            return $e->withFormField('third', 'doesnt exist');
        });
    }
}