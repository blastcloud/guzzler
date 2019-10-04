<?php

namespace tests\testFiles;

use BlastCloud\Chassis\Filters\Base;
use BlastCloud\Chassis\Interfaces\With;

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