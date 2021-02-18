<?php

declare (strict_types=1);
/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WPCOM_VIP\Humbug\PhpScoper;

use WPCOM_VIP\JetBrains\PHPStormStub\PhpStormStubsMap;
use function array_fill_keys;
use function array_keys;
use function array_merge;
use function strtolower;
/**
 * @private
 */
final class Reflector
{
    private const MISSING_CLASSES = [
        // https://github.com/JetBrains/phpstorm-stubs/pull/594
        'WPCOM_VIP\\parallel\\Channel',
        'WPCOM_VIP\\parallel\\Channel\\Error',
        'WPCOM_VIP\\parallel\\Channel\\Error\\Closed',
        'WPCOM_VIP\\parallel\\Channel\\Error\\Existence',
        'WPCOM_VIP\\parallel\\Channel\\Error\\IllegalValue',
        'WPCOM_VIP\\parallel\\Error',
        'WPCOM_VIP\\parallel\\Events',
        'WPCOM_VIP\\parallel\\Events\\Error',
        'WPCOM_VIP\\parallel\\Events\\Error\\Existence',
        'WPCOM_VIP\\parallel\\Events\\Error\\Timeout',
        'WPCOM_VIP\\parallel\\Events\\Event',
        'WPCOM_VIP\\parallel\\Events\\Event\\Type',
        'WPCOM_VIP\\parallel\\Events\\Input',
        'WPCOM_VIP\\parallel\\Events\\Input\\Error',
        'WPCOM_VIP\\parallel\\Events\\Input\\Error\\Existence',
        'WPCOM_VIP\\parallel\\Events\\Input\\Error\\IllegalValue',
        'WPCOM_VIP\\parallel\\Future',
        'WPCOM_VIP\\parallel\\Future\\Error',
        'WPCOM_VIP\\parallel\\Future\\Error\\Cancelled',
        'WPCOM_VIP\\parallel\\Future\\Error\\Foreign',
        'WPCOM_VIP\\parallel\\Future\\Error\\Killed',
        'WPCOM_VIP\\parallel\\Runtime',
        'WPCOM_VIP\\parallel\\Runtime\\Bootstrap',
        'WPCOM_VIP\\parallel\\Runtime\\Error',
        'WPCOM_VIP\\parallel\\Runtime\\Error\\Bootstrap',
        'WPCOM_VIP\\parallel\\Runtime\\Error\\Closed',
        'WPCOM_VIP\\parallel\\Runtime\\Error\\IllegalFunction',
        'WPCOM_VIP\\parallel\\Runtime\\Error\\IllegalInstruction',
        'WPCOM_VIP\\parallel\\Runtime\\Error\\IllegalParameter',
        'WPCOM_VIP\\parallel\\Runtime\\Error\\IllegalReturn',
    ];
    private const MISSING_FUNCTIONS = [];
    private const MISSING_CONSTANTS = [
        'STDIN',
        'STDOUT',
        'STDERR',
        // Added in PHP 7.4
        'T_BAD_CHARACTER',
        'T_FN',
        'T_COALESCE_EQUAL',
        // Added in PHP 8.0
        'T_NAME_QUALIFIED',
        'T_NAME_FULLY_QUALIFIED',
        'T_NAME_RELATIVE',
        'T_MATCH',
        'T_NULLSAFE_OBJECT_OPERATOR',
        'T_ATTRIBUTE',
    ];
    private static $CLASSES;
    private static $FUNCTIONS;
    private static $CONSTANTS;
    /**
     * @param array<string,string>|null $symbols
     * @param array<string,string>      $source
     * @param string[]                  $missingSymbols
     */
    private static function initSymbolList(?array &$symbols, array $source, array $missingSymbols) : void
    {
        if (null !== $symbols) {
            return;
        }
        $symbols = \array_fill_keys(\array_merge(\array_keys($source), $missingSymbols), \true);
    }
    public function __construct()
    {
        self::initSymbolList(self::$CLASSES, \WPCOM_VIP\JetBrains\PHPStormStub\PhpStormStubsMap::CLASSES, self::MISSING_CLASSES);
        self::initSymbolList(self::$FUNCTIONS, \WPCOM_VIP\JetBrains\PHPStormStub\PhpStormStubsMap::FUNCTIONS, self::MISSING_FUNCTIONS);
        self::initSymbolList(self::$CONSTANTS, \WPCOM_VIP\JetBrains\PHPStormStub\PhpStormStubsMap::CONSTANTS, self::MISSING_CONSTANTS);
    }
    public function isClassInternal(string $name) : bool
    {
        return isset(self::$CLASSES[$name]);
    }
    public function isFunctionInternal(string $name) : bool
    {
        return isset(self::$FUNCTIONS[\strtolower($name)]);
    }
    public function isConstantInternal(string $name) : bool
    {
        return isset(self::$CONSTANTS[$name]);
    }
}
