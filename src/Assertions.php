<?php

namespace Guzzler;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

trait Assertions
{
    /** @var TestCase */
    protected $testInstance;

    protected $history = [];

    protected function increment()
    {
        $this->testInstance->addToAssertionCount(1);
    }

    /**
     * Assert that no requests have been called on the client.
     *
     * @param null|string $message
     */
    public function assertNoHistory($message = null)
    {
        $this->assertHistoryCount(
            0,
            $message ?? 'Failed asserting that Guzzle has no history.'
        );
    }

    /**
     * Assert that the specified number of requests have been made.
     *
     * @param int $count
     * @param null|string $message
     */
    public function assertHistoryCount(int $count, $message = null)
    {
        $r = $count == 1 ? 'request' : 'requests';

        $this->assert(
            count($this->history) == $count,
            $message ?? "Failed asserting that Guzzle received {$count} {$r}."
        );
    }

    /**
     * @param array $indexes
     * @throws UndefinedIndexException
     * @return array
     */
    protected function findOrFailIndexes(array $indexes)
    {
        return array_map(function ($i) {
            if (!isset($this->history[$i])) {
                throw new UndefinedIndexException("Guzzle history does not have a [{$i}] index.");
            }

            return $this->history[$i];
        }, $indexes);
    }

    /**
     * Run filters from the closure Expectation with a specific subset of history.
     *
     * @param array $history
     * @param \Closure $closure
     * @return mixed
     */
    protected function runClosure(array $history, \Closure $closure)
    {
        $e = new Expectation();
        $closure($e);

        return (function ($h) {
            return $this->runFilters($h);
        })->call($e, $history);
    }

    /**
     * This is really just a convenience method to save a few repeated lines
     * for each assert method.
     *
     * @param bool $test
     * @param $message
     */
    protected function assert(bool $test, $message)
    {
        if (!$test) {
            Assert::fail($message);
        }

        $this->increment();
    }

    /**
     * Assert that the first request meets expectations.
     *
     * @param \Closure $closure
     * @param null|string $message
     * @throws UndefinedIndexException
     */
    public function assertFirst(\Closure $closure, $message = null)
    {
        $h = $this->runClosure(
            $this->findOrFailIndexes([0]),
            $closure
        );

        $this->assert(
            count($h) == 1,
            $message ?? 'Failed asserting that the first request met expectations.'
        );
    }

    /**
     * Assert that the last request meets expectations.
     *
     * @param \Closure $closure
     * @param null $message
     * @throws UndefinedIndexException
     */
    public function assertLast(\Closure $closure, $message = null)
    {
        $h = $this->runClosure(
            $this->findOrFailIndexes([
                max(array_keys($this->history))
            ]),
            $closure
        );

        $this->assert(
            count($h) == 1,
            $message ?? 'Failed asserting that the last request met expectations.'
        );
    }

    /**
     * Assert that every request, regardless of count, meet expectations.
     *
     * @param \Closure $closure
     * @param null $message
     * @throws UndefinedIndexException
     */
    public function assertAll(\Closure $closure, $message = null)
    {
        if (empty($this->history)) {
            throw new UndefinedIndexException("Guzzle history is currently empty.");
        }

        $this->assertIndexes(array_keys($this->history), $closure, $message);
    }

    public function assertIndexes(array $indexes, \Closure $closure, $message = null)
    {
        $h = $this->runClosure(
            $this->findOrFailIndexes($indexes),
            $closure
        );

        $diff = array_diff($indexes, array_keys($h));

        $this->assert(
            empty($diff),
            $message ?? "Failed asserting that indexes [".implode(',', $diff)."] met expectations."
        );
    }

    public function assertNone(\Closure $closure, $message = null)
    {
        $h = $this->runClosure($this->history, $closure);

        $this->assert(
            empty($h),
            $message ?? 'Failed asserting that no history items met expectations. '.
                'Indexes ['.implode(',', array_keys($h)).'] met expectations.'
        );
    }
}