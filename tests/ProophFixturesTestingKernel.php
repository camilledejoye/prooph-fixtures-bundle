<?php

namespace Prooph\Bundle\Fixtures\Tests;

use Prooph\Bundle\Fixtures\ProophFixturesBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class ProophFixturesTestingKernel extends Kernel
{
    const FIXTURES_LOCATOR_ID = 'test.prooph_fixtures.fixtures_locator';

    /** @var callable */
    private $registerServicesCallback;

    public function registerBundles() : array
    {
        return [
            new ProophFixturesBundle(),
        ];
    }

    public function registerServices(callable $callback) : void
    {
        $this->registerServicesCallback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container): void {
            $this->getRegisterServicesCallback()($container);

            $container->setParameter('prooph_event_store.projection_managers', []);

            $container->setAlias(
                self::FIXTURES_LOCATOR_ID,
                new Alias('prooph_fixtures.fixtures_locator', true)
            );
        });
    }

    public function getCacheDir()
    {
        return sprintf('%s/%s', parent::getCacheDir(), spl_object_hash($this));
    }

    private function getRegisterServicesCallback()
    {
        if (is_callable($this->registerServicesCallback)) {
            return $this->registerServicesCallback;
        }

        return static function(ContainerBuilder $container) {
        };
    }
}