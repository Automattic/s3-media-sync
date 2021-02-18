<?php

declare (strict_types=1);
namespace WPCOM_VIP;

use WPCOM_VIP\Symfony\Component\DependencyInjection\ContainerBuilder;
use WPCOM_VIP\Symfony\Component\DependencyInjection\Reference;
require_once __DIR__ . '/vendor/autoload.php';
interface Salute
{
    public function salute() : string;
}
\class_alias('WPCOM_VIP\\Salute', 'Salute', \false);
class Foo implements \WPCOM_VIP\Salute
{
    private $bar;
    public function __construct(\WPCOM_VIP\Bar $bar)
    {
        $this->bar = $bar;
    }
    public function salute() : string
    {
        return $this->bar->salute();
    }
}
\class_alias('WPCOM_VIP\\Foo', 'Foo', \false);
class Bar implements \WPCOM_VIP\Salute
{
    public function salute() : string
    {
        return "Hello world!";
    }
}
\class_alias('WPCOM_VIP\\Bar', 'Bar', \false);
$container = new \WPCOM_VIP\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\WPCOM_VIP\Foo::class, \WPCOM_VIP\Foo::class)->addArgument(new \WPCOM_VIP\Symfony\Component\DependencyInjection\Reference(\WPCOM_VIP\Bar::class))->setPublic(\true);
$container->register(\WPCOM_VIP\Bar::class, \WPCOM_VIP\Bar::class);
$container->compile();
echo $container->get(\WPCOM_VIP\Foo::class)->salute() . \PHP_EOL;
