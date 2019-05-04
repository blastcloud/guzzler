<?php

namespace BlastCloud\Guzzler\Helpers;

use BlastCloud\Guzzler\Traits\Helpers;

/**
 * Class File
 * @package BlastCloud\Guzzler\Helpers
 * @property-write $contents
 * @property-write string $contentType
 * @property-write string $filename
 * @property-write array $headers
 */
class File implements \JsonSerializable
{
    use Helpers;

    protected $contents;
    protected $contentType;
    protected $filename;
    protected $headers = [];

    public function __construct($contents = null, string $filename = null, string $contentType = null, array $headers = [])
    {
        $this->contents = $this->resolveContent($contents);
        $this->filename = $filename;
        $this->contentType = $contentType;
        $this->headers = $headers;
    }

    public static function create(array $fields)
    {
        return new self(
            $fields['contents'] ?? null,
            $fields['filename'] ?? null,
            $fields['contentType'] ?? null,
            $fields['headers'] ?? []
        );
    }

    protected function resolveContent($file)
    {
        if (is_resource($file)) {
            $f = $file;
            $file = stream_get_contents($file);
            fclose($f);
        }

        return $file;
    }

    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw new \InvalidArgumentException("The $name property does not exist on the File object.");
        }

        if ($name == 'contents') {
            $value = $this->resolveContent($value);
        }

        $this->$name = $value;
    }

    public function jsonSerialize()
    {
        return [
            'filename' => $this->filename,
            'contentType' => $this->contentType,
            'headers' => $this->headers,
            'contents' => $this->contents
        ];
    }

    public function compare(Disposition $d)
    {
        foreach (['contents', 'filename', 'contentType'] as $att) {
            if ($f = $this->get($att, $d) != $d->$att) {
                die(var_dump($f, $d->$att));
                return [$f, $d->$att];
            }
        }
        return $this->compareHeaders($d);
    }

    protected function compareHeaders(Disposition $d): bool
    {
        foreach ($this->headers as $key => $value) {
            if ($this->arrayMissing($key, $value, $d->headers))
            {
                return false;
            }
        }

        return true;
    }

    protected function get($name, Disposition $default)
    {
        return $this->$name ?? $default->$name;
    }
}