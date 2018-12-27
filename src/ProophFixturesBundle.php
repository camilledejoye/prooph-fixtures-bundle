<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures;

use Prooph\Bundle\Fixtures\DependencyInjection\Compiler\FixturesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ProophFixturesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FixturesPass());
    }
}
