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

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->configureCleaners($config['cleaners'], $container);
    }

    private function configureCleaners(array $config, ContainerBuilder $container): void
    {
        $defaultCleanerBatchSize = $config['default']['batch_size'] ?? null;
        $eventStreamsCleanerBatchSize = $config['event_streams']['batch_size'] ?? $defaultCleanerBatchSize;
        $projectionsCleanerBatchSize = $config['projections']['batch_size'] ?? $defaultCleanerBatchSize;

        if ($eventStreamsCleanerBatchSize) {
            $container->getDefinition('prooph_fixtures.event_streams_cleaner')
                ->setArgument(1, $eventStreamsCleanerBatchSize);
        }

        if ($projectionsCleanerBatchSize) {
            $container->getDefinition('prooph_fixtures.projections_cleaner')
                ->setArgument(2, $projectionsCleanerBatchSize);
        }
    }
}
