<?php

declare (strict_types=1);
namespace WPCOM_VIP;

$autoload = __DIR__ . '/../vendor/scoper-autoload.php';
if (\false === \file_exists($autoload)) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
}
require_once $autoload;
use WPCOM_VIP\Set011\DirectionaryLocator;
use WPCOM_VIP\Set011\Greeter;
use WPCOM_VIP\Set011\Dictionary;
$dir = \Phar::running(\false);
if ('' === $dir) {
    // Running outside of a PHAR
    $dir = __DIR__ . \DIRECTORY_SEPARATOR . 'bin';
}
$testDir = \dirname($dir) . '/../tests';
$dictionaries = \WPCOM_VIP\Set011\DirectionaryLocator::locateDictionaries($testDir);
$words = \array_reduce($dictionaries, function (array $words, \WPCOM_VIP\Set011\Dictionary $dictionary) : array {
    $words = \array_merge($words, $dictionary->provideWords());
    return $words;
}, []);
$greeter = new \WPCOM_VIP\Set011\Greeter($words);
foreach ($greeter->greet() as $greeting) {
    echo $greeting . \PHP_EOL;
}
