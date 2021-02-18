<?php

declare (strict_types=1);
namespace WPCOM_VIP;

/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return ['meta' => [
    'title' => 'Whitelisting functions which are never declared',
    // Default values. If not specified will be the one used
    'prefix' => 'Humbug',
    'whitelist' => [],
    'whitelist-global-constants' => \false,
    'whitelist-global-classes' => \false,
    'whitelist-global-functions' => \false,
    'registered-classes' => [],
    'registered-functions' => [],
], 'Non whitelisted global function call' => <<<'PHP'
<?php

main();
----
<?php

namespace Humbug;

\Humbug\main();

PHP
, 'Whitelisted global function call' => ['whitelist' => ['main'], 'registered-functions' => [['main', 'WPCOM_VIP\\Humbug\\main']], 'payload' => <<<'PHP'
<?php

main();
----
<?php

namespace Humbug;

\Humbug\main();

PHP
], 'Global function call with whitelisted global functions' => ['whitelist-global-functions' => \true, 'registered-functions' => [['main', 'WPCOM_VIP\\Humbug\\main']], 'payload' => <<<'PHP'
<?php

main();
----
<?php

namespace Humbug;

\Humbug\main();

PHP
], 'Global function call with non-whitelisted global functions' => <<<'PHP'
<?php

main();
----
<?php

namespace Humbug;

\Humbug\main();

PHP
, 'Whitelisted namespaced function call' => [
    'whitelist' => ['WPCOM_VIP\\Acme\\main'],
    'registered-functions' => [],
    // Nothing registered here since the FQ could not be resolved
    'payload' => <<<'PHP'
<?php

namespace Acme;

main();
----
<?php

namespace Humbug\Acme;

main();

PHP
,
]];
