<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\DependencyInjection\Compiler;

use Prooph\Fixtures\Locator\InMemoryFixturesLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FixturesPass implements CompilerPassInterface
{
    const FIXTURE_TAG = 'prooph_fixtures.fixtures';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $taggedFixtures = $container->findTaggedServiceIds(self::FIXTURE_TAG);

        $fixtures = [];
        foreach ($taggedFixtures as $fixtureId => $tags) {
            $fixtures[] = new Reference($fixtureId);
        }

        $container->register('prooph_fixtures.fixtures_locator', InMemoryFixturesLocator::class)
            ->addArgument($fixtures);
    }
}
