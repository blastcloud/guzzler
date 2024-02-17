<?php

namespace BlastCloud\Guzzler;

use BlastCloud\Chassis\Helpers\File;

/**
 * Class Expectation
 * @package Guzzler
 * @method $this endpoint(string $uri, string $method)
 * @method $this get(string $uri)
 * @method $this post(string $uri)
 * @method $this put(string $uri)
 * @method $this delete(string $uri)
 * @method $this patch(string $uri)
 * @method $this options(string $uri)
 * @method $this synchronous()
 * @method $this asynchronous()
 * @method $this withHeader(string $key, $value)
 * @method $this withHeaders(array $values)
 * @method $this withOption(string $key, $value)
 * @method $this withOptions(array $values)
 * @method $this withQuery(array $values, bool $exclusive = false)
 * @method $this withoutQuery()
 * @method $this withQueryKey(string $key)
 * @method $this withQueryKeys(array $keys)
 * @method $this withRpc(string $endpoint, string $method, array $params, ?string $id)
 * @method $this withJson(array $values, bool $exclusive = false)
 * @method $this withForm(array $form, bool $exclusive = false)
 * @method $this withFormField(string $key, $value)
 * @method $this withBody($body, bool $exclusive = false)
 * @method $this withEndpoint(string $uri, string $method)
 * @method $this withFile(string $field, File $file)
 * @method $this withFiles(array $files, bool $exclusive = false)
 * @method $this withCallback(\Closure $callback, string $message = null)
 */
class Expectation extends \BlastCloud\Chassis\Expectation
{
    public function synchronous()
    {
        return $this->withOption('synchronous', true);
    }

    public function asynchronous()
    {
        return $this->withOption('synchronous', null);
    }

    public function get($uri)
    {
        return $this->withEndpoint($uri, 'GET');
    }

    public function put($uri)
    {
        return $this->withEndpoint($uri, 'PUT');
    }

    public function post($uri)
    {
        return $this->withEndpoint($uri, 'POST');
    }

    public function delete($uri)
    {
        return $this->withEndpoint($uri, 'DELETE');
    }

    public function patch($uri)
    {
        return $this->withEndpoint($uri, 'PATCH');
    }

    public function options($uri)
    {
        return $this->withEndpoint($uri, 'OPTIONS');
    }

    public function endpoint($url, $method)
    {
        return $this->withEndpoint($url, strtoupper($method));
    }

    public function withoutQuery()
    {
        return $this->withQuery([], true);
    }
}
