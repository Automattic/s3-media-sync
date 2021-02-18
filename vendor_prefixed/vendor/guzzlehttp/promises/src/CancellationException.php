<?php

namespace WPCOM_VIP\GuzzleHttp\Promise;

/**
 * Exception that is set as the reason for a promise that has been cancelled.
 */
class CancellationException extends \WPCOM_VIP\GuzzleHttp\Promise\RejectionException
{
}
