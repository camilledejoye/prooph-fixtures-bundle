<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DummyBundle;

use Symfony\Component\DependencyInjection\ServiceLocator;

class DummyProjectionManagersLocator extends ServiceLocator
{
    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        /* return new DummyProjectionManager(); */
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return 'test_manager' === $id;
    }
}
