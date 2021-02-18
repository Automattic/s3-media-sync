<?php

declare (strict_types=1);
namespace WPCOM_VIP\PhpParser\Lexer\TokenEmulator;

use WPCOM_VIP\PhpParser\Lexer\Emulative;
final class MatchTokenEmulator extends \WPCOM_VIP\PhpParser\Lexer\TokenEmulator\KeywordEmulator
{
    public function getPhpVersion() : string
    {
        return \WPCOM_VIP\PhpParser\Lexer\Emulative::PHP_8_0;
    }
    public function getKeywordString() : string
    {
        return 'match';
    }
    public function getKeywordToken() : int
    {
        return \T_MATCH;
    }
}
