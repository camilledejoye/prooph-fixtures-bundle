<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Tests;

use DummyBundle\DummyBundle;
use Prooph\Bundle\Fixtures\ProophFixturesBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class ProophFixturesTestingKernel extends Kernel
{
    const FIXTURES_PROVIDER_ID = 'test.prooph_fixtures.fixtures_provider';

    /** @var callable */
    private $registerServicesCallback;

    public function registerBundles(): array
    {
        return [
            new ProophFixturesBundle(),
            new DummyBundle(),
        ];
    }

    public function registerServices(callable $callback): void
    {
        $this->registerServicesCallback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/Fixtures/DummyBundle/Resources/config/services.yaml');

        $loader->load($this->getRegisterServicesCallback());
    }

    public function getCacheDir()
    {
        return \sprintf('%s/%s', parent::getCacheDir(), \spl_object_hash($this));
    }

    private function getRegisterServicesCallback()
    {
        if (! \is_callable($this->registerServicesCallback)) {
            $this->registerServicesCallback = static function (ContainerBuilder $container) {
            };
        }

        return $this->registerServicesCallback;
    }
}
