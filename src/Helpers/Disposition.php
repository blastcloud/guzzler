<?php

namespace BlastCloud\Guzzler\Helpers;

class Disposition
{
    protected $value;
    protected $filename;
    protected $contentType;
    protected $contentLength;
    protected $name;
    protected $headers = [];

    public function __construct(string $body)
    {
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $body) as $line) {
            // There is a blank line between fields and the value of the disposition.
            if(empty($line)) {
                break;
            }

            $start = strtok($line, ':');
            $method = strtolower(str_replace('-', '_', $start));
            $end = substr($line, strlen($start) + 2);

            // If method for this key exists, pass the rest of the line.
            if (method_exists($this, $method)) {
                $this->$method($end);
                continue;
            }

            // Otherwise, it's a header.
            $this->headers[$start] = $end;
        }

        $this->value = substr($body, strlen($body) - $this->contentLength);
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function isFile(): bool
    {
        return !empty($this->contentType) || !empty($this->filename);
    }

    protected function content_disposition($line)
    {
        foreach (explode(';', $line) as $datum) {
            $parts = explode('=', trim($datum));

            if (property_exists($this, $parts[0])) {
                $this->{$parts[0]} = trim($parts[1], '"');
            }
        }
    }

    protected function content_length($line)
    {
        $this->contentLength = (int)$line;
    }

    protected function content_type($line)
    {
        $this->contentType = $line;
    }
}