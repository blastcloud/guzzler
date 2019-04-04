<?php

namespace BlastCloud\Guzzler\Helpers;

use PHPUnit\Runner\BeforeFirstTestHook;
use BlastCloud\Guzzler\Expectation;

final class Extension implements BeforeFirstTestHook
{
    const NAMESPACE = 'GuzzlerFilterNamespace';
    const MACRO_FILE = 'GuzzlerMacroFile';

    protected $macroFiles = [];

    /**
     * @throws \Exception
     */
    public function executeBeforeFirstTest(): void
    {
        if ($namespace = $GLOBALS[self::NAMESPACE] ?? false) {
            Expectation::addNamespace($namespace);
        }

        if ($file = $GLOBALS[self::MACRO_FILE] ?? false) {
            $this->macroFiles[] = $file;
        }

        $this->loadMacros();
    }

    public function loadMacros()
    {
        foreach ($this->macroFiles as $file) {
            if (!is_file($file)) {
                throw new \Exception("The macro file {$file} cannot be found.");
            }

            if (!is_readable($file)) {
                throw new \Exception("The macro file {$file} cannot be read.");
            }

            require_once $file;
        }
    }
}