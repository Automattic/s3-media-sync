<?php

declare (strict_types=1);
namespace WPCOM_VIP\Acme;

final class Foo
{
    public function __invoke()
    {
        echo 'OK';
    }
}
