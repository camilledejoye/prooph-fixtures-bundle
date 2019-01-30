<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Projection;

use Prooph\Bundle\EventStore\Projection\Projection;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\Bundle\Fixtures\Projection\Exception\ProjectionManagerNotFound;
use Prooph\Bundle\Fixtures\Projection\Exception\ReadModelNotFound;
use Prooph\EventStore\Exception\ProjectionNotFound;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\Projector;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ProjectionFactory
{
    /**
     * @var ServiceLocator
     */
    private $projectionsLocator;

    /**
     * @var ServiceLocator
     */
    private $projectionManagersLocator;

    /**
     * @var ServiceLocator
     */
    private $readModelsLocator;

    public function __construct(
        ServiceLocator $projectionsLocator,
        ServiceLocator $projectionManagersLocator,
        ServiceLocator $readModelsLocator
    ) {
        $this->projectionsLocator = $projectionsLocator;
        $this->projectionManagersLocator = $projectionManagersLocator;
        $this->readModelsLocator = $readModelsLocator;
    }

    /**
     * Create a projection from its name.
     *
     * @param string $projectionName
     *
     * @return Projector|ReadModelProjector
     *
     * @throws ProjectionNotFound The projection was not found.
     * @throws ProjectionManagerNotFound The projection manager of the projection was not found.
     */
    public function createByName(string $projectionName)
    {
        $projection = $this->getProjectionByName($projectionName);
        $projector = $this->getProjectorByProjectionName($projectionName, $projection);

        return $projection->project($projector);
    }

    /**
     * Gets a projection by its name.
     *
     * @param string $projectionName
     *
     * @return Projection|ReadModelProjection
     *
     * @throws ProjectionNotFound The projection was not found.
     */
    private function getProjectionByName(string $projectionName)
    {
        if (! $this->projectionsLocator->has($projectionName)) {
            throw ProjectionNotFound::withName($projectionName);
        }

        return $this->projectionsLocator->get($projectionName);
    }

    /**
     * Gets the projector for a projection by its name.
     *
     * @param string $projectionName
     * @param Projection|ReadModelProjection $projection
     *
     * @return Projector|ReadModelProjector
     *
     * @throws ProjectionManagerNotFound The projection manager was not found.
     * @throws ReadModelNotFound The read model was not found.
     */
    private function getProjectorByProjectionName(string $projectionName, $projection)
    {
        $projectionManager = $this->getProjectionManagerByProjectionName($projectionName);

        if ($projection instanceof ReadModelProjection) {
            $readModel = $this->getReadModelByProjectionName($projectionName);
            $projector = $projectionManager->createReadModelProjection(
                $projectionName,
                $readModel
            );
        } else {
            $projector = $projectionManager->createProjection($projectionName);
        }

        return $projector;
    }

    /**
     * Gets the projection manager of a projection by its name.
     *
     * @param string $projectionName
     *
     * @return ProjectionManager
     *
     * @throws ProjectionManagerNotFound The projection manager was not found.
     */
    private function getProjectionManagerByProjectionName(string $projectionName): ProjectionManager
    {
        if (! $this->projectionManagersLocator->has($projectionName)) {
            throw ProjectionManagerNotFound::withProjectionName($projectionName);
        }

        return $this->projectionManagersLocator->get($projectionName);
    }

    /**
     * Gets the read model of a projection by its name.
     *
     * @param string $projectionName
     *
     * @return ReadModel
     *
     * @throws ReadModelNotFound The read model was not found.
     */
    private function getReadModelByProjectionName(string $projectionName): ReadModel
    {
        if (! $this->readModelsLocator->has($projectionName)) {
            throw ReadModelNotFound::withProjectionName($projectionName);
        }

        return $this->readModelsLocator->get($projectionName);
    }
}
