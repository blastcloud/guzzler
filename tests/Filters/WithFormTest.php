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
}