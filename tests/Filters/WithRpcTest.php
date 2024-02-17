<?php

namespace Tests\Filters;


use BlastCloud\Guzzler\Expectation;
use BlastCloud\Guzzler\Filters\WithRpc;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use tests\ExceptionMessageRegex;

class WithRpcTest extends TestCase
{
    use UsesGuzzler, ExceptionMessageRegex;

    /** @var Client */
    public $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->guzzler->getClient();
    }

    public function testWithRpc()
    {
        $url = '/rpc';
        $id = '123';
        $method = 'test-method';
        $params = [
            'a' => 1,
            'b' => 2,
        ];

        $this->guzzler->queueMany(new Response(), 2);

        $this->guzzler->expects($this->atLeastOnce())
            ->withRpc($url, $method, $params, $id);

        $this->client->post($url, [
            RequestOptions::JSON => $this->makeJsonRPCRequest($method, $params, $id),
        ]);

        $this->client->post($url, [
            RequestOptions::JSON => $this->makeJsonRPCRequest($method, [], $id),
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->{self::$regexMethodName}("/JSON-RPC 2.0/");
        $this->guzzler->assertLast(function (Expectation $e) {
            return $e->withRpc('/rpc', 'test-method', ['a' => 1, 'b' => 2], 123);
        });
    }

    /**
     * @param $method
     * @param $params
     * @param $id
     * @return array
     */
    protected function makeJsonRPCRequest($method, $params, $id)
    {
        return [
            'jsonrpc' => WithRpc::JSON_RPC_VERSION,
            'method' => $method,
            'params' => $params,
            'id' => $id,
        ];
    }
}
