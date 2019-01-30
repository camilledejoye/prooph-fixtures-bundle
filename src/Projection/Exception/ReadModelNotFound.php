<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Projection\Exception;

use Prooph\Bundle\Fixtures\Exception\RuntimeException;

final class ReadModelNotFound extends RuntimeException
{
    public static function withProjectionName(string $projectionName): self
    {
        return new self(\sprintf(
            'No read model found for the projection named "%s".',
            $projectionName
        ));
    }
}
