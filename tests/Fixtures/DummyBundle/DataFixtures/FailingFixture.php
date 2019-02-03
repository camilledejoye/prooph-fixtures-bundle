<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DummyBundle\DataFixtures;

use Prooph\Fixtures\Fixture\Fixture;
use RuntimeException;

class FailingFixture implements Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(): void
    {
        throw new RuntimeException('Something went wrong!');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'FailingFixture';
    }
}
