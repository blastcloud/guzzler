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
        if (count($this->history) !== $count) {
            Assert::fail($message ?? 'Failed asserting that Guzzle received '.$count.' '.($count > 1 ? 'requests.' : 'request.'));
        }

        $this->increment();
    }

    /**
     * @param array $indexes
     * @throws UndefinedIndexException
     * @return array
     */
    protected function findOrFailIndexes(array $indexes)
    {
        return array_filter($indexes, function ($index) {
            die(var_dump($index));
        });

        $r = [];

        foreach ($indexes as $i) {
            if (!isset($this->history[$i])) {
                throw new UndefinedIndexException("Guzzle history does not have a {$i} index.");
            }
            $r[$i] = $this->history[$i];
        }

        return $r;
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
            count($h) !== 1,
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
            $this->findOrFailIndexes([count($this->history) - 1]),
            $closure
        );

        $this->assert(
            count($h) !== 1,
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

        $h = $this->runClosure($this->history, $closure);

        $this->assert(
            count($h) !== count($this->history),
            $message ?? 'Failed asserting that all requests met expectations.'
        );
    }

    public function assertIndexes(array $indexes, \Closure $closure, $message = null)
    {
        $h = $this->runClosure(
            $this->findOrFailIndexes($indexes),
            $closure
        );

        $diff = array_diff_assoc($indexes, array_keys($h));

        if (count($diff)) {
            Assert::fail($message ?? "Failed asserting that indexes ".implode(',', $diff)." met expectations");
        }

        $this->increment();
    }
}