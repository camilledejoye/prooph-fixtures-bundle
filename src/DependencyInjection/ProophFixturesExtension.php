<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\DependencyInjection;

use Prooph\Bundle\Fixtures\DependencyInjection\Compiler\FixturesPass;
use Prooph\Fixtures\Fixture\Fixture;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ProophFixturesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container->registerForAutoconfiguration(Fixture::class)
            ->addTag(FixturesPass::FIXTURE_TAG);
    }
}
