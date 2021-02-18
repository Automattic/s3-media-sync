<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser;

interface ErrorHandler
{
    /**
     * Handle an error generated during lexing, parsing or some other operation.
     *
     * @param Error $error The error that needs to be handled
     */
    public function handleError(\WPCOM_VIP\PhpParser\Error $error);
}
