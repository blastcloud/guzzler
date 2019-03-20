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

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testForm()
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
}