<?php

declare (strict_types=1);
namespace WPCOM_VIP;

use WPCOM_VIP\Symfony\Component\Finder\Finder;
use WPCOM_VIP\Symfony\Component\Finder\SplFileInfo;
require_once __DIR__ . '/vendor/autoload.php';
$finder = \WPCOM_VIP\Symfony\Component\Finder\Finder::create()->files()->in(__DIR__)->depth(0)->sortByName();
foreach ($finder as $fileInfo) {
    /** @var SplFileInfo $fileInfo */
    echo $fileInfo->getFilename() . \PHP_EOL;
}
