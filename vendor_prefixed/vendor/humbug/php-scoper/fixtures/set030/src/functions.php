<?php

declare (strict_types=1);
namespace WPCOM_VIP;

function foo() : bool
{
    return \true;
}
if (!\function_exists('WPCOM_VIP\\bar')) {
    function bar() : bool
    {
        return \true;
    }
}
if (\function_exists('WPCOM_VIP\\baz')) {
    \WPCOM_VIP\baz();
}
