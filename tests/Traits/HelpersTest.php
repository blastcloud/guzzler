<?php

namespace tests\Traits;

use BlastCloud\Guzzler\Traits\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->instance = new class {
            use Helpers;
        };
    }

    public function testPluckArray()
    {
        $original = [
            [
                'title' => 'first',
                'prop' => 'one'
            ],
            [
                'title' => 'second'
            ],
            [
                'title' => 'third',
                'prop' => 'three'
            ]
        ];

        $results = $this->instance->pluck($original, 'prop');

        $this->assertCount(2, $results);
        $this->assertContains('one', $results);
        $this->assertContains('three', $results);
    }

    public function testPluckObject()
    {
        $original = [
            (object) [
                'title' => 'first',
                'prop' => 'one'
            ],
            (object) [
                'title' => 'second'
            ],
            (object) [
                'title' => 'third',
                'prop' => 'three'
            ]
        ];

        $results = $this->instance->pluck($original, 'prop');

        $this->assertCount(2, $results);
        $this->assertContains('one', $results);
        $this->assertContains('three', $results);
    }
}