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
    'title' => 'Abstract class declaration',
    // Default values. If not specified will be the one used
    'prefix' => 'Humbug',
    'whitelist' => [],
    'whitelist-global-constants' => \true,
    'whitelist-global-classes' => \false,
    'whitelist-global-functions' => \true,
    'registered-classes' => [],
    'registered-functions' => [],
], 'Declaration in the global namespace' => <<<'PHP'
<?php

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}

PHP
, 'Declaration in the global namespace with global classes whitelisted' => ['whitelist-global-classes' => \true, 'registered-classes' => [['A', 'WPCOM_VIP\\Humbug\\A']], 'payload' => <<<'PHP'
<?php

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}
\class_alias('Humbug\\A', 'A', \false);

PHP
], 'Declaration in the global namespace with the global namespace which is namespaced whitelisted' => ['whitelist' => ['\\*'], 'payload' => <<<'PHP'
<?php

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace {
    abstract class A
    {
        public function a()
        {
        }
        public abstract function b();
    }
}

PHP
], 'Declaration of a whitelisted class in the global namespace' => ['whitelist' => ['A'], 'registered-classes' => [['A', 'WPCOM_VIP\\Humbug\\A']], 'payload' => <<<'PHP'
<?php

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}
\class_alias('Humbug\\A', 'A', \false);

PHP
], 'Declaration of a whitelisted class in the global namespace which is whitelisted' => ['whitelist' => ['A', '\\*'], 'registered-classes' => [['A', 'WPCOM_VIP\\Humbug\\A']], 'payload' => <<<'PHP'
<?php

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace {
    abstract class A
    {
        public function a()
        {
        }
        public abstract function b();
    }
}

PHP
], 'Declaration in a namespace' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug\Foo;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}

PHP
, 'Declaration in a namespace with global classes whitelisted' => ['whitelist-global-classes' => \true, 'payload' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug\Foo;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}

PHP
], 'Declaration in a whitelisted namespace' => ['whitelist' => ['Foo\\*'], 'payload' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Foo;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}

PHP
], 'Declaration of a whitelisted class in a namespace' => ['whitelist' => ['WPCOM_VIP\\Foo\\A'], 'registered-classes' => [['WPCOM_VIP\\Foo\\A', 'WPCOM_VIP\\Humbug\\Foo\\A']], 'payload' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug\Foo;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}
\class_alias('Humbug\\Foo\\A', 'Foo\\A', \false);

PHP
], 'Declaration of a namespaced class whitelisted with a pattern' => ['whitelist' => ['Foo\\A*'], 'registered-classes' => [['WPCOM_VIP\\Foo\\A', 'WPCOM_VIP\\Humbug\\Foo\\A'], ['WPCOM_VIP\\Foo\\AA', 'WPCOM_VIP\\Humbug\\Foo\\AA'], ['WPCOM_VIP\\Foo\\A\\B', 'WPCOM_VIP\\Humbug\\Foo\\A\\B']], 'payload' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
}

abstract class AA {}

abstract class B {}

namespace Foo\A;

abstract class B {}

----
<?php

namespace Humbug\Foo;

abstract class A
{
    public function a()
    {
    }
}
\class_alias('Humbug\\Foo\\A', 'Foo\\A', \false);
abstract class AA
{
}
\class_alias('Humbug\\Foo\\AA', 'Foo\\AA', \false);
abstract class B
{
}
namespace Humbug\Foo\A;

abstract class B
{
}
\class_alias('Humbug\\Foo\\A\\B', 'Foo\\A\\B', \false);

PHP
], 'Declaration of a whitelisted class in a namespace with FQCN for the whitelist' => ['whitelist' => ['WPCOM_VIP\\Foo\\A'], 'registered-classes' => [['WPCOM_VIP\\Foo\\A', 'WPCOM_VIP\\Humbug\\Foo\\A']], 'payload' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Humbug\Foo;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}
\class_alias('Humbug\\Foo\\A', 'Foo\\A', \false);

PHP
], 'Declaration of a class belonging to a whitelisted namespace' => ['whitelist' => ['\\*'], 'payload' => <<<'PHP'
<?php

namespace Foo;

abstract class A {
    public function a() {}
    abstract public function b();
}
----
<?php

namespace Foo;

abstract class A
{
    public function a()
    {
    }
    public abstract function b();
}

PHP
], 'Multiple declarations in different namespaces with whitelisted classes' => ['whitelist' => ['WPCOM_VIP\\Foo\\WA', 'WPCOM_VIP\\Bar\\WB', 'WC'], 'registered-classes' => [['WPCOM_VIP\\Foo\\WA', 'WPCOM_VIP\\Humbug\\Foo\\WA'], ['WPCOM_VIP\\Bar\\WB', 'WPCOM_VIP\\Humbug\\Bar\\WB'], ['WC', 'WPCOM_VIP\\Humbug\\WC']], 'payload' => <<<'PHP'
<?php

namespace Foo {

    abstract class A {
        public function a() {}
    }

    abstract class WA {
        public function a() {}
    }
}

namespace Bar {

    abstract class B {
        public function b() {}
    }

    abstract class WB {
        public function b() {}
    }
}

namespace {

    abstract class C {
        public function c() {}
    }

    abstract class WC {
        public function c() {}
    }
}
----
<?php

namespace Humbug\Foo;

abstract class A
{
    public function a()
    {
    }
}
abstract class WA
{
    public function a()
    {
    }
}
\class_alias('Humbug\\Foo\\WA', 'Foo\\WA', \false);
namespace Humbug\Bar;

abstract class B
{
    public function b()
    {
    }
}
abstract class WB
{
    public function b()
    {
    }
}
\class_alias('Humbug\\Bar\\WB', 'Bar\\WB', \false);
namespace Humbug;

abstract class C
{
    public function c()
    {
    }
}
abstract class WC
{
    public function c()
    {
    }
}
\class_alias('Humbug\\WC', 'WC', \false);

PHP
]];
