<?php

namespace WPCOM_VIP\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
