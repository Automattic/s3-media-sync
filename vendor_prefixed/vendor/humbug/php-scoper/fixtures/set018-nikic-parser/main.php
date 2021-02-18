<?php

declare (strict_types=1);
namespace WPCOM_VIP;

use WPCOM_VIP\PhpParser\NodeDumper;
use WPCOM_VIP\PhpParser\ParserFactory;
require_once __DIR__ . '/vendor/autoload.php';
$code = <<<'CODE'
<?php

namespace WPCOM_VIP;

function test($foo)
{
    \var_dump($foo);
}
CODE;
$parser = (new \WPCOM_VIP\PhpParser\ParserFactory())->create(\WPCOM_VIP\PhpParser\ParserFactory::PREFER_PHP7);
$ast = $parser->parse($code);
$dumper = new \WPCOM_VIP\PhpParser\NodeDumper();
echo $dumper->dump($ast) . "\n";
