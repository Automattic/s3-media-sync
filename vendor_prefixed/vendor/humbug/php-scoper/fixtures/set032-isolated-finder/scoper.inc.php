<?php

namespace WPCOM_VIP;

use WPCOM_VIP\Isolated\Symfony\Component\Finder\Finder;
return ['finders' => [(new \WPCOM_VIP\Isolated\Symfony\Component\Finder\Finder())->files()->in(__DIR__ . \DIRECTORY_SEPARATOR . 'dir')]];
