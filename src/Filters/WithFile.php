<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Helpers\Disposition;
use BlastCloud\Guzzler\Helpers\File;
use BlastCloud\Guzzler\Traits\Helpers;
use BlastCloud\Guzzler\Interfaces\With;
use GuzzleHttp\Psr7\MultipartStream;

class WithFile extends Base implements With
{
    use Helpers;

    protected $files = [];
    protected $exclusive = false;

    public function withFile(string $name, File $file)
    {
        $this->files[$name] = $file;
    }

    public function withFiles(array $files, bool $exclusive = false)
    {
        foreach ($files as $key => $file) {
            $this->withFile($key, $file);
        }

        $this->exclusive = $exclusive;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($item) {
            $body = $item['request']->getBody();

            if (!$body instanceof MultipartStream) {
                return false;
            }

            $dispositions = [];

            foreach ($this->parseMultipartBody($body) as $d) {
                if ($d->isFile()) {
                    $dispositions[$d->name] = $d;
                }
            }

            foreach ($this->files as $name => $file) {
                if (!isset($dispositions[$name]) || !$file->compare($dispositions[$name])) {
                    return false;
                }
            }

            return !$this->exclusive || count($dispositions) == count($this->files);
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "Files: (Exclusive: {$e}) ".json_encode($this->files, JSON_PRETTY_PRINT);
    }
}