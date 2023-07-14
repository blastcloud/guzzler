<?php

namespace tests;

trait ExceptionMessageRegex
{
    /** @var string */
    public static $regexMethodName = 'expectExceptionMessageMatches';

    public static function setUpBeforeClass(): void
    {
        if (!method_exists(self::class, self::$regexMethodName)) {
            self::$regexMethodName = 'expectExceptionMessageRegExp';
        }
    }
}