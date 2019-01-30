<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Cleaner;

use Prooph\Bundle\Fixtures\Projection\ProjectionFactory;
use Prooph\EventStore\Exception\ProjectionNotFound;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ProjectionStatus;
use Prooph\Fixtures\Cleaner\CleaningProjectionStrategy;
use Prooph\Fixtures\Cleaner\Exception\CleaningProjectionFailed;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Implementation of the strategy to clean projections from the PDO
 * EventStore implementation.
 * In this implementation you should use the ProjectionManager to clean
 * running projections and the projection directly otherwise.
 *
 * @see CleaningProjectionStrategy
 */
class PdoCleaningProjectionStrategy implements CleaningProjectionStrategy
{
    /**
     * @var ServiceLocator
     */
    private $projectionManagersLocator;

    /**
     * @var ProjectionFactory
     */
    private $projectionFactory;

    public function __construct(
        ServiceLocator $projectionManagersLocator,
        ProjectionFactory $projectionFactory
    ) {
        $this->projectionManagersLocator = $projectionManagersLocator;
        $this->projectionFactory = $projectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function clean(string $projectionName): void
    {
        try {
            $this->resetAProjection($projectionName);
        } catch (Throwable $error) {
            if ($error instanceof ProjectionNotFound) {
                throw $error;
            }

            throw new CleaningProjectionFailed(
                $error->getMessage(),
                $error->getCode(),
                $error
            );
        }
    }

    /**
     * Reset a projection.
     *
     * @param string $projectionName
     *
     * @return void
     */
    private function resetAProjection(string $projectionName): void
    {
        $projectionManager = $this->getAProjectionManagerByProjectionName($projectionName);

        $isProjectionRunning = $projectionManager
            ->fetchProjectionStatus($projectionName)
            ->is(ProjectionStatus::RUNNING);

        if ($isProjectionRunning) {
            $projectionManager->resetProjection($projectionName);
        } else {
            $this->getAProjecorByProjectionName($projectionName)
                ->reset();
        }
    }

    /**
     * Gets a projection manager for a projection by its name.
     *
     * @param string $projectionName
     *
     * @return ProjectionManager
     *
     * @throws NotFoundExceptionInterface No projection manager defined for the projection name.
     */
    private function getAProjectionManagerByProjectionName(string $projectionName): ProjectionManager
    {
        return $this->projectionManagersLocator->get($projectionName);
    }

    /**
     * Gets the projector of a projection by its name.
     *
     * @param string $projectionName
     *
     * @throws ProjectionNotFound The projection was not found.
     * @throws ProjectionManagerNotFound The projection manager of the projection was not found.
     */
    private function getAProjecorByProjectionName(string $projectionName)
    {
        return $this->projectionFactory->createByName($projectionName);
    }
}
