<?php

namespace YoastSEO_Vendor\GuzzleHttp\Promise;

/**
 * Represents a promise that iterates over many promises and invokes
 * side-effect functions in the process.
 */
class EachPromise implements \YoastSEO_Vendor\GuzzleHttp\Promise\PromisorInterface
{
    private $pending = [];
    /** @var \Iterator */
    private $iterable;
    /** @var callable|int */
    private $concurrency;
    /** @var callable */
    private $onFulfilled;
    /** @var callable */
    private $onRejected;
    /** @var Promise */
    private $aggregate;
    /** @var bool */
    private $mutex;
    /**
     * Configuration hash can include the following key value pairs:
     *
     * - fulfilled: (callable) Invoked when a promise fulfills. The function
     *   is invoked with three arguments: the fulfillment value, the index
     *   position from the iterable list of the promise, and the aggregate
     *   promise that manages all of the promises. The aggregate promise may
     *   be resolved from within the callback to short-circuit the promise.
     * - rejected: (callable) Invoked when a promise is rejected. The
     *   function is invoked with three arguments: the rejection reason, the
     *   index position from the iterable list of the promise, and the
     *   aggregate promise that manages all of the promises. The aggregate
     *   promise may be resolved from within the callback to short-circuit
     *   the promise.
     * - concurrency: (integer) Pass this configuration option to limit the
     *   allowed number of outstanding concurrently executing promises,
     *   creating a capped pool of promises. There is no limit by default.
     *
     * @param mixed    $iterable Promises or values to iterate.
     * @param array    $config   Configuration options
     */
    public function __construct($iterable, array $config = [])
    {
        $this->iterable = iter_for($iterable);
        if (isset($config['concurrency'])) {
            $this->concurrency = $config['concurrency'];
        }
        if (isset($config['fulfilled'])) {
            $this->onFulfilled = $config['fulfilled'];
        }
        if (isset($config['rejected'])) {
            $this->onRejected = $config['rejected'];
        }
    }
    public function promise()
    {
        if ($this->aggregate) {
            return $this->aggregate;
        }
        try {
            $this->createPromise();
            $this->iterable->rewind();
            $this->refillPending();
        } catch (\Throwable $e) {
            $this->aggregate->reject($e);
        } catch (\Exception $e) {
            $this->aggregate->reject($e);
        }
        return $this->aggregate;
    }
    private function createPromise()
    {
        $this->mutex = \false;
        $this->aggregate = new \YoastSEO_Vendor\GuzzleHttp\Promise\Promise(function () {
            \reset($this->pending);
            if (empty($this->pending) && !$this->iterable->valid()) {
                $this->aggregate->resolve(null);
                return;
            }
            // 更多精品WP资源尽在喵容：miaoroom.com
//Consume a potentially fluctuating list of promises while
            // 更多精品WP资源尽在喵容：miaoroom.com
//ensuring that indexes are maintained (precluding array_shift).
            while ($promise = \current($this->pending)) {
                \next($this->pending);
                $promise->wait();
                if ($this->aggregate->getState() !== \YoastSEO_Vendor\GuzzleHttp\Promise\PromiseInterface::PENDING) {
                    return;
                }
            }
        });
        // 更多精品WP资源尽在喵容：miaoroom.com
//Clear the references when the promise is resolved.
        $clearFn = function () {
            $this->iterable = $this->concurrency = $this->pending = null;
            $this->onFulfilled = $this->onRejected = null;
        };
        $this->aggregate->then($clearFn, $clearFn);
    }
    private function refillPending()
    {
        if (!$this->concurrency) {
            // 更多精品WP资源尽在喵容：miaoroom.com
//Add all pending promises.
            while ($this->addPending() && $this->advanceIterator()) {
            }
            return;
        }
        // 更多精品WP资源尽在喵容：miaoroom.com
//Add only up to N pending promises.
        $concurrency = \is_callable($this->concurrency) ? \call_user_func($this->concurrency, \count($this->pending)) : $this->concurrency;
        $concurrency = \max($concurrency - \count($this->pending), 0);
        // 更多精品WP资源尽在喵容：miaoroom.com
//Concurrency may be set to 0 to disallow new promises.
        if (!$concurrency) {
            return;
        }
        // 更多精品WP资源尽在喵容：miaoroom.com
//Add the first pending promise.
        $this->addPending();
        // 更多精品WP资源尽在喵容：miaoroom.com
//Note this is special handling for concurrency=1 so that we do
        // 更多精品WP资源尽在喵容：miaoroom.com
//not advance the iterator after adding the first promise. This
        // 更多精品WP资源尽在喵容：miaoroom.com
//helps work around issues with generators that might not have the
        // 更多精品WP资源尽在喵容：miaoroom.com
//next value to yield until promise callbacks are called.
        while (--$concurrency && $this->advanceIterator() && $this->addPending()) {
        }
    }
    private function addPending()
    {
        if (!$this->iterable || !$this->iterable->valid()) {
            return \false;
        }
        $promise = promise_for($this->iterable->current());
        $idx = $this->iterable->key();
        $this->pending[$idx] = $promise->then(function ($value) use($idx) {
            if ($this->onFulfilled) {
                \call_user_func($this->onFulfilled, $value, $idx, $this->aggregate);
            }
            $this->step($idx);
        }, function ($reason) use($idx) {
            if ($this->onRejected) {
                \call_user_func($this->onRejected, $reason, $idx, $this->aggregate);
            }
            $this->step($idx);
        });
        return \true;
    }
    private function advanceIterator()
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
//Place a lock on the iterator so that we ensure to not recurse,
        // 更多精品WP资源尽在喵容：miaoroom.com
//preventing fatal generator errors.
        if ($this->mutex) {
            return \false;
        }
        $this->mutex = \true;
        try {
            $this->iterable->next();
            $this->mutex = \false;
            return \true;
        } catch (\Throwable $e) {
            $this->aggregate->reject($e);
            $this->mutex = \false;
            return \false;
        } catch (\Exception $e) {
            $this->aggregate->reject($e);
            $this->mutex = \false;
            return \false;
        }
    }
    private function step($idx)
    {
        // 更多精品WP资源尽在喵容：miaoroom.com
//If the promise was already resolved, then ignore this step.
        if ($this->aggregate->getState() !== \YoastSEO_Vendor\GuzzleHttp\Promise\PromiseInterface::PENDING) {
            return;
        }
        unset($this->pending[$idx]);
        // 更多精品WP资源尽在喵容：miaoroom.com
//Only refill pending promises if we are not locked, preventing the
        // 更多精品WP资源尽在喵容：miaoroom.com
//EachPromise to recursively invoke the provided iterator, which
        // 更多精品WP资源尽在喵容：miaoroom.com
//cause a fatal error: "Cannot resume an already running generator"
        if ($this->advanceIterator() && !$this->checkIfFinished()) {
            // 更多精品WP资源尽在喵容：miaoroom.com
//Add more pending promises if possible.
            $this->refillPending();
        }
    }
    private function checkIfFinished()
    {
        if (!$this->pending && !$this->iterable->valid()) {
            // 更多精品WP资源尽在喵容：miaoroom.com
//Resolve the promise if there's nothing left to do.
            $this->aggregate->resolve(null);
            return \true;
        }
        return \false;
    }
}
