<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\ErrorHandler;

use WPCOM_VIP\PhpParser\Error;
use WPCOM_VIP\PhpParser\ErrorHandler;
/**
 * Error handler that handles all errors by throwing them.
 *
 * This is the default strategy used by all components.
 */
class Throwing implements \WPCOM_VIP\PhpParser\ErrorHandler
{
    public function handleError(\WPCOM_VIP\PhpParser\Error $error)
    {
        throw $error;
    }
}
