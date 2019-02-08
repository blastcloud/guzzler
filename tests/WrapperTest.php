<?php

use PHPUnit\Framework\TestCase;
use Guzzler\Wrapper;
use GuzzleHttp\Client;

class WrapperTest extends TestCase
{
    /** @var Wrapper */
    public $wrap;

    public function setUp(): void
    {
        parent::setUp();

        $this->wrap = new Wrapper($this);
    }

    public function testGetClientReturnsProperInstance()
    {
        $this->assertInstanceOf(Client::class, $this->wrap->getClient());
    }

    public function testGetClientMergesConfigs()
    {
        $configs = [
            'stream' => true,
            'verify' => false
        ];

        $client = $this->wrap->getClient($configs);
        $settings = $client->getConfig();

        foreach ($configs as $key => $value) {
            $this->assertEquals($value, $settings[$key]);
        }
    }

    public function testGetHandlerStackReturnsHandlerStack()
    {
        $this->assertInstanceOf(\GuzzleHttp\HandlerStack::class, $this->wrap->getHandlerStack());
    }
}