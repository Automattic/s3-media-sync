<?php

namespace WPCOM_VIP\JetBrains\PhpStorm\Internal;

use Attribute;
use WPCOM_VIP\JetBrains\PhpStorm\Deprecated;
use WPCOM_VIP\JetBrains\PhpStorm\ExpectedValues;
/**
 * For PhpStorm internal use only
 * @since 8.0
 * @internal
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class PhpStormStubsElementAvailable
{
    public function __construct()
    {
    }
}
