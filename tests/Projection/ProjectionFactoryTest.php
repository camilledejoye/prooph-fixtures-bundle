<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Tests\Projection;

use PHPUnit\Framework\TestCase;
use Prooph\Bundle\EventStore\Projection\Projection;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\Bundle\Fixtures\Projection\Exception\ProjectionManagerNotFound;
use Prooph\Bundle\Fixtures\Projection\Exception\ReadModelNotFound;
use Prooph\Bundle\Fixtures\Projection\ProjectionFactory;
use Prooph\EventStore\Exception\ProjectionNotFound;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\Projector;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ProjectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_fails_to_create_a_projection_which_does_not_exist()
    {
        $projectionName = 'unknown projection name';

        $this->expectException(ProjectionNotFound::class);
        $this->expectExceptionMessage(\sprintf(
            'A projection with name "%s" could not be found.',
            $projectionName
        ));

        $projectionFactory = $this->createAProjectionFactory([]);
        $projectionFactory->createByName($projectionName);
    }

    /**
     * @test
     */
    public function it_fails_to_create_a_projection_which_with_no_manager()
    {
        $projectionName = 'projection without manager';

        $this->expectException(ProjectionManagerNotFound::class);
        $this->expectExceptionMessage(\sprintf(
            'No projection manager found for the projection named "%s".',
            $projectionName
        ));

        $projectionFactory = $this->createAProjectionFactory(
            [$projectionName => $this->createAProjection()],
            // To avoid the creation of a projection manager for the projection under test
            ['other projection name' => $this->createAProjectionManagerForProjections([])]
        );

        $projectionFactory->createByName($projectionName);
    }

    /**
     * @test
     */
    public function it_fails_to_create_a_read_model_projection_without_a_read_model()
    {
        $projectionName = 'read model projection without read model';

        $this->expectException(ReadModelNotFound::class);
        $this->expectExceptionMessage(\sprintf(
            'No read model found for the projection named "%s".',
            $projectionName
        ));

        $projectionFactory = $this->createAProjectionFactory(
            [$projectionName => $this->createAReadModelProjection()],
            [], // To have a global projection manager
            // To avoid the creation of a read model for the projection under test
            ['other projection name' => $this->createAReadModel()]
        );

        $projectionFactory->createByName($projectionName);
    }

    /**
     * @test
     * @dataProvider provideValidProjectionNames
     */
    public function it_creates_a_projection_by_name(string $projectionType, string $projectionName)
    {
        $projectionFactory = $this->createAProjectionFactory([
            $projectionName => $this->createAProjectionByType(
                Projector::class === $projectionType
                ? Projection::class
                : ReadModelProjection::class
            ),
        ]);

        $this->assertInstanceOf(
            $projectionType,
            $projectionFactory->createByName($projectionName)
        );
    }

    public function provideValidProjectionNames(): array
    {
        return [
            'standard projection' => [Projector::class, 'standard projection'],
            'read model projection' => [ReadModelProjector::class, 'read model projection'],
        ];
    }

    private function createAProjectionFactory(
        array $projections,
        array $projectionManagers = [],
        array $readModels = []
    ): ProjectionFactory {
        $projectionsNames = \array_keys($projections);

        if (! $projectionManagers) {
            $projectionManagers = $this->createAProjectionManagersMapForProjections(
                $projectionsNames
            );
        }

        if (! $readModels) {
            $readModels = $this->createAReadModelsMapForProjections($projectionsNames);
        }

        return new ProjectionFactory(
            $this->createAServiceLocatorFor($projections),
            $this->createAServiceLocatorFor($projectionManagers),
            $this->createAServiceLocatorFor($readModels)
        );
    }

    private function createAServiceLocatorFor(array $services): ServiceLocator
    {
        $serviceLocator = $this->createMock(ServiceLocator::class);

        $serviceLocator->expects($this->any())
            ->method('has')
            ->willReturnCallback(function (string $serviceId) use ($services) {
                return \array_key_exists($serviceId, $services);
            });

        $serviceLocator->expects($this->any())
            ->method('get')
            ->willReturnMap(\array_map(
                function (string $serviceId, $service) {
                    return [$serviceId, $service];
                },
                \array_keys($services),
                \array_values($services)
            ));

        return $serviceLocator;
    }

    private function createAProjectionManagerForProjections(array $projectionsNames): ProjectionManager
    {
        $projectionManager = $this->getMockForAbstractClass(ProjectionManager::class);

        $projectionManager->expects($this->any())
            ->method('createProjection')
            ->willReturn($this->createAProjector());

        $projectionManager->expects($this->any())
            ->method('createReadModelProjection')
            ->willReturn($this->createAReadModelProjector());

        $projectionManager->expects($this->any())
            ->method('fetchProjectionNames')
            ->willReturnOnConsecutiveCalls(\array_merge(
                \array_chunk($projectionsNames, 2),
                [[]]
            ));

        return $projectionManager;
    }

    private function createAReadModel(): ReadModel
    {
        return $this->getMockForAbstractClass(ReadModel::class);
    }

    private function createAProjection(): Projection
    {
        return $this->createAProjectionByType(Projection::class);
    }

    private function createAReadModelProjection(): ReadModelProjection
    {
        return $this->createAProjectionByType(ReadModelProjection::class);
    }

    private function createAProjectionByType(string $type)
    {
        $projection = $this->getMockForAbstractClass($type);

        $projection->expects($this->any())
            ->method('project')
            ->willReturnArgument(0);

        return $projection;
    }

    private function createAProjector(): Projector
    {
        return $this->getMockForAbstractClass(Projector::class);
    }

    private function createAReadModelProjector(): ReadModelProjector
    {
        return $this->getMockForAbstractClass(ReadModelProjector::class);
    }

    private function createAProjectionManagersMapForProjections(array $projectionsNames)
    {
        $globalProjectionManager = $this->createAProjectionManagerForProjections($projectionsNames);

        $projectionManagers = [];
        foreach ($projectionsNames as $projectionName) {
            $projectionManagers[$projectionName] = $globalProjectionManager;
        }

        return $projectionManagers;
    }

    private function createAReadModelsMapForProjections(array $projectionsNames)
    {
        $readModels = [];
        foreach ($projectionsNames as $projectionName) {
            $readModels[$projectionName] = $this->createAReadModel();
        }

        return $readModels;
    }
}
