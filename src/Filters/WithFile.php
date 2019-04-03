<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Helpers\Helpers;
use BlastCloud\Guzzler\Interfaces\With;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Stream;

class WithFile extends Base implements With
{
    use Helpers;

    protected $files = [];
    protected $fileName;
    protected $mime;
    protected $exclusive = false;

    public function withFile($formField, $file, $filename = null, $mime = null)
    {
        $this->files[$formField] = $file;
        $this->fileName = $filename ?? $this->fileName;
        $this->mime = $mime ?? $this->mime;
    }

    public function withFileName($filename)
    {
        $this->fileName = $filename;
    }

    public function withFileMime($mime)
    {
        $this->mime = $mime;
    }

    public function withFiles(array $files, bool $exclusive = false)
    {
        foreach ($files as $key => $file) {
            $this->files[$key] = $file;
        }

        $this->exclusive = $exclusive;
    }

    protected function filterByFieldsAndFileContents($dispositions)
    {
        $fields = $this->pluck('name', $dispositions);

        foreach ($this->files as $field => $file) {
            // Field Names
            if (!in_array($field, $fields)) {
                return false;
            }

            // Resources
            if (is_resource($file)) {
                $f = stream_get_contents($file);
                fclose($file);
                $file = $f;
            }

            // File name passed
            if (realpath($file)) {
                $file = file_get_contents($file);
            }

            foreach ($dispositions as $d) {
                if ($d->value == $file) {
                    return true;
                }
            }
        }

        return null;
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($item) {
            $body = $item['request']->getBody();

            if (!$body instanceof MultipartStream
                && !$body instanceof Stream) {
                return false;
            }

            $dispositions = array_filter($this->parseMultipartBody($body), function ($d) {
                return $d; //$d->isFile();
            });

            $res = $this->filterByFieldsAndFileContents($dispositions);
            if ($res !== null) {
                return $res;
            }




            return false;
        });
    }

    public function __toString(): string
    {
        $e = $this->exclusive ? 'true' : 'false';
        $str = "File: (Exclusive: {$e})".json_encode($this->files);

        if (!empty($this->fileName)) {
            $str .= "\n" . str_pad("FileName:", self::STR_PAD) . $this->fileName;
        }

        if (!empty($this->mime)) {
            $str .= "\n" . str_pad("Mime:", self::STR_PAD) . $this->mime;
        }

        return $str;
    }
}