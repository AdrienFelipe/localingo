<?php

namespace App\Shared\Infrastructure;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    public const FRAMEWORK_DIR = '/app/active-framework';
    use MicroKernelTrait;

    private static function configPath(): string
    {
        return self::FRAMEWORK_DIR . '/config';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import(self::configPath() . '/{packages}/*.yaml');
        $container->import(self::configPath() . '/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(self::configPath() . '/services.yaml')) {
            $container->import(self::configPath() . '/services.yaml');
            $container->import(self::configPath() . '/{services}_' . $this->environment . '.yaml');
        } else {
            $container->import(self::configPath() . '/{services}.php');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(self::configPath() . '/{routes}/' . $this->environment . '/*.yaml');
        $routes->import(self::configPath() . '/{routes}/*.yaml');

        if (is_file(self::configPath() . '/routes.yaml')) {
            $routes->import(self::configPath() . '/routes.yaml');
        } else {
            $routes->import(self::configPath() . '/{routes}.php');
        }
    }

    public function getProjectDir(): string
    {
        return self::FRAMEWORK_DIR;
    }
}
