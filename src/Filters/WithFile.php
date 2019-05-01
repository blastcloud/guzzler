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
            if (!isset($item['dispositions'])) {
                return false;
            }

            foreach ($this->files as $name => $file) {
                if (!isset($item['dispositions'][$name]) || !$file->compare($item['dispositions'][$name])) {
                    return false;
                }
            }

            return true;
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        return "Files: (Exclusive: {$e}) ".json_encode($this->files, JSON_PRETTY_PRINT);
    }
}