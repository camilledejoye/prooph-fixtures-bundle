<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Projection;

use Generator;
use Prooph\Bundle\EventStore\Projection\Projection;
use Prooph\EventStore\Projection\ProjectionManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Provides the names of all the projections.
 */
class ProjectionsNamesProvider
{
    /**
     * @var ServiceLocator
     */
    private $projectionManagersLocator;

    /**
     * @var iterable
     */
    private $projectionManagersNames;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var string[]
     */
    private $projectionsNames;

    public function __construct(
        ServiceLocator $projectionManagersLocator,
        iterable $projectionManagersNames,
        int $batchSize = 10000
    ) {
        $this->projectionManagersLocator = $projectionManagersLocator;
        $this->projectionManagersNames = \array_keys($projectionManagersNames);
        $this->batchSize = $batchSize;
        $this->projectionsNames = [];
    }

    /**
     * Gets all the projections names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        if (! $this->projectionsNames) {
            $this->projectionsNames = \iterator_to_array($this->getAllProjectionsNames(), false);
        }

        return $this->projectionsNames;
    }

    /**
     * Gets the projection manager by it's own name.
     *
     * @param string $projectionManagerName
     *
     * @return ProjectionManager
     *
     * @throws NotFoundExceptionInterface The projection manager's name was not found.
     * @throws ContainerExceptionInterface Error while retrieving the projection manager.
     */
    private function getProjectionManagerByItsName(string $projectionManagerName): ProjectionManager
    {
        return $this->projectionManagersLocator->get($projectionManagerName);
    }

    /**
     * Gets all the projection managers.
     *
     * @return ProjectionManager[]
     *
     * @throws NotFoundExceptionInterface A projection's name was not found.
     * @throws ContainerExceptionInterface Error while retrieving a projection manager.
     */
    private function getAllProjectionManagers(): Generator
    {
        foreach ($this->projectionManagersNames as $projectionManagerName) {
            yield $this->getProjectionManagerByItsName($projectionManagerName);
        }
    }

    /**
     * Gets the names of all the projections.
     *
     * @return string[]
     */
    private function getAllProjectionsNames(): Generator
    {
        foreach ($this->getAllProjectionManagers() as $projectionManager) {
            yield from $this->getProjectionsNamesForManager($projectionManager);
        }
    }

    /**
     * Gets the names of all the projections managed by a projection manager.
     *
     * @param ProjectionManager $projectionManager
     *
     * @return string[]
     */
    private function getProjectionsNamesForManager(ProjectionManager $projectionManager): Generator
    {
        $offset = 0;
        while ($projectionNames = $projectionManager->fetchProjectionNames(null, $this->batchSize, $offset)) {
            yield from $projectionNames;

            $offset += $this->batchSize;
        }
    }
}
