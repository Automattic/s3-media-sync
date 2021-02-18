<?php

namespace WPCOM_VIP\App;

use WPCOM_VIP\Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use WPCOM_VIP\Symfony\Component\Config\Loader\LoaderInterface;
use WPCOM_VIP\Symfony\Component\Config\Resource\FileResource;
use WPCOM_VIP\Symfony\Component\DependencyInjection\ContainerBuilder;
use WPCOM_VIP\Symfony\Component\HttpKernel\Kernel as BaseKernel;
use WPCOM_VIP\Symfony\Component\Routing\RouteCollectionBuilder;
class Kernel extends \WPCOM_VIP\Symfony\Component\HttpKernel\Kernel
{
    use MicroKernelTrait;
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';
    public function getCacheDir()
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }
    public function getLogDir()
    {
        return $this->getProjectDir() . '/var/log';
    }
    public function registerBundles()
    {
        $contents = (require $this->getProjectDir() . '/config/bundles.php');
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                (yield new $class());
            }
        }
    }
    protected function configureContainer(\WPCOM_VIP\Symfony\Component\DependencyInjection\ContainerBuilder $container, \WPCOM_VIP\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $container->addResource(new \WPCOM_VIP\Symfony\Component\Config\Resource\FileResource($this->getProjectDir() . '/config/bundles.php'));
        // Feel free to remove the "container.autowiring.strict_mode" parameter
        // if you are using symfony/dependency-injection 4.0+ as it's the default behavior
        $container->setParameter('container.autowiring.strict_mode', \true);
        $container->setParameter('container.dumper.inline_class_loader', \true);
        $confDir = $this->getProjectDir() . '/config';
        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }
    protected function configureRoutes(\WPCOM_VIP\Symfony\Component\Routing\RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir() . '/config';
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }
}
