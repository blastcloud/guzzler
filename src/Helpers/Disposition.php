<?php

namespace BlastCloud\Guzzler\Helpers;

class Disposition
{
    public $value;
    public $filename;
    public $contentType;
    public $contentLength;
    public $name;
    public $headers = [];

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

    public function isFile(): bool
    {
        return !empty($this->contentType);
    }

    protected function content_disposition($line)
    {
        preg_match("/name=\"(.+)\"/", $line, $matches);
        $this->name = $matches[1];

        preg_match("/filename=\"(.+)\"/", $line, $matches);
        $this->filename = $matches[1] ?? null;
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