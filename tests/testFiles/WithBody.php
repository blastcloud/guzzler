<?php

namespace tests\testFiles;

use BlastCloud\Guzzler\Filters\WithBody as Base;
use BlastCloud\Chassis\Interfaces\With;
use BlastCloud\Guzzler\UsesGuzzler;

class WithBody extends Base implements With
{
    use UsesGuzzler;

    public static $bodyString;

    public function withBody($body, bool $exclusive = false)
    {
        self::$bodyString = $body;
        parent::withBody($body, $exclusive);
    }
}