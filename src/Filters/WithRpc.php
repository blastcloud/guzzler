<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Chassis\Filters\Base;
use BlastCloud\Guzzler\Traits\RecursiveSort;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class WithRpc extends Base implements With
{

    use RecursiveSort;

    const JSON_RPC_VERSION = '2.0';

    /** @var UriInterface */
    protected $url = '';

    /** @var array */
    protected $json = [];

    public function withRpc(string $url, string $method, array $params, ?string $id = null)
    {
        $this->url = Uri::fromParts(parse_url($url));

        $json = [
            'jsonrpc' => self::JSON_RPC_VERSION,
            'method' => $method,
            'params' => $params,
        ];

        if ($id) {
            $json['id'] = $id;
        }

        $this->json = $json;

        $this->sort($this->json);
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($call) {

            /** @var RequestInterface $request */
            $request = $call['request'];

            $isEndpointCorrect = $request->getMethod() === 'POST'
                && $request->getUri()->getPath() === $this->url->getPath();

            $body = json_decode($request->getBody(), true);

            $this->sort($body);

            $j1 = json_encode($body);
            $j2 = json_encode($this->json);

            $isJsonCorrect = $j1 === $j2;

            return $isEndpointCorrect && $isJsonCorrect;
        });
    }

    public function __toString(): string
    {
        return "JSON-RPC 2.0: (POST {$this->url}) "
            .json_encode($this->json, JSON_PRETTY_PRINT);
    }
}
