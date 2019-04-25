<?php

namespace BlastCloud\Guzzler\Filters;

use BlastCloud\Guzzler\Traits\Helpers;
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

    public function withFile($formField, $file)
    {
        $this->files[$formField] = $file;
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
        //die(var_dump($dispositions));
    }

    public function __invoke(array $history): array
    {
        return array_filter($history, function ($item) {
            $body = $item['request']->getBody();

            if (!$body instanceof MultipartStream) {
                return false;
            }

            $dispositions = array_filter($this->parseMultipartBody($body), function ($d) {
                return $d->isFile();
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