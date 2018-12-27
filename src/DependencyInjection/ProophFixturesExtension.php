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
        $config = $this->processConfig($configs, $container);

        $this->loadServices($container);
        $this->addFixturesAutoconfiguration($container);
        $this->configureCleaners($config['cleaners'], $container);
    }

    private function processConfig(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        return $config;
    }

    private function loadServices(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    private function addFixturesAutoconfiguration(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(Fixture::class)
            ->addTag('prooph_fixtures.fixtures');
    }

    private function configureCleaners(array $config, ContainerBuilder $container): void
    {
        $defaultBatchSize      = $config['default']['batch_size'] ?? null;
        $eventStreamsBatchSize = $config['event_streams']['batch_size'] ?? $defaultBatchSize;
        $projectionsBatchSize  = $config['projections']['batch_size'] ?? $defaultBatchSize;

        if ($eventStreamsBatchSize) {
            $container->getDefinition('prooph_fixtures.event_streams_cleaner')
                ->setArgument(1, $eventStreamsBatchSize);
        }

        if ($projectionsBatchSize) {
            $container->getDefinition('prooph_fixtures.projections_cleaner')
                ->setArgument(2, $projectionsBatchSize);
        }
    }
}
