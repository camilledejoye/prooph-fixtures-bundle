<?php

namespace Prooph\Bundle\Fixtures\Tests\Fixtures;

use Prooph\Fixtures\Fixture\Fixture;

class AFixture implements Fixture
{
    /**
     * {@inheritDoc}
     */
    public function load(): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'AFixture';
    }
}
