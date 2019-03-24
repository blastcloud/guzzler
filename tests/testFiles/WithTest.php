<?php

namespace tests\testFiles;

use BlastCloud\Guzzler\Filters\Base;
use BlastCloud\Guzzler\Interfaces\With;

class WithTest extends Base implements With
{
    public static $first;
    public static $second;

    public function withTest($first, $second)
    {
        self::$first = $first;
        self::$second = $second;
    }

    public function __invoke(array $history): array
    {
        return $history;
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }

}