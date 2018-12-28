<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Tests\Fixtures;

use Iterator;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;

class DummyEventStore implements EventStore
{
    public function fetchStreamMetadata(StreamName $streamName): array
    {
    }

    public function hasStream(StreamName $streamName): bool
    {
    }

    public function load(StreamName $streamName, int $fromNumber = 1, int $count = null, MetadataMatcher $metadataMatcher = null): Iterator
    {
    }

    public function loadReverse(StreamName $streamName, int $fromNumber = null, int $count = null, MetadataMatcher $metadataMatcher = null): Iterator
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamNames(?string $filter, ?MetadataMatcher $metadataMatcher, int $limit = 20, int $offset = 0): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamNamesRegex(?string $filter, ?MetadataMatcher $metadataMatcher, int $limit = 20, int $offset = 0): array
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCategoryNames(?string $filter, int $limit = 20, int $offset = 0): array
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCategoryNamesRegex(string $filter, int $limit = 20, int $offset = 0): array
    {
    }

    public function updateStreamMetadata(StreamName $streamName, array $newMetadata): void
    {
    }

    public function create(Stream $stream): void
    {
    }

    public function appendTo(StreamName $streamName, Iterator $streamEvents): void
    {
    }

    public function delete(StreamName $streamName): void
    {
    }
}
