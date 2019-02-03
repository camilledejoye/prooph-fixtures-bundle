<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Tests\Cleaner;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prooph\Bundle\Fixtures\Cleaner\PdoCleaningProjectionStrategy;
use Prooph\Bundle\Fixtures\Projection\ProjectionFactory;
use Prooph\EventStore\Exception\ProjectionNotFound;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ProjectionStatus;
use Prooph\EventStore\Projection\Projector;
use Prooph\EventStore\Projection\ReadModelProjector;
use Prooph\Fixtures\Cleaner\Exception\CleaningProjectionFailed;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PdoCleaningProjectionStrategyTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideProjectors
     */
    public function it_cleans_a_running_projection(string $projectionName, MockObject $projector)
    {
        $projectionManager = $this->createAProjectionManager(ProjectionStatus::RUNNING());
        $projectionManagersLocator = $this->createAProjectionManagersLocator($projectionManager);
        $projectionFactory = $this->createAProjectionFactory($projector);

        $cleaningStrategy = $this->createASut($projectionManagersLocator, $projectionFactory);

        $projectionManager->expects($this->once())
            ->method('resetProjection')
            ->with($this->identicalTo($projectionName));

        $cleaningStrategy->clean($projectionName);
    }

    /**
     * @test
     * @dataProvider provideProjectors
     */
    public function it_cleans_a_not_running_projection(string $projectionName, MockObject $projector)
    {
        // Don't know why but without this I have: This test did not perform any assertions
        // But if I change once() to never() then I takes it into account...
        $projector = $projector instanceof Projector
            ? $this->createAProjector()
            : $this->createAReadModelProjector();

        $projectionManager = $this->createAProjectionManager(ProjectionStatus::IDLE());
        $projectionManagersLocator = $this->createAProjectionManagersLocator($projectionManager);
        $projectionFactory = $this->createAProjectionFactory($projector);

        $cleaningStrategy = $this->createASut($projectionManagersLocator, $projectionFactory);

        $projector->expects($this->once())
            ->method('reset');

        $cleaningStrategy->clean($projectionName);
    }

    /**
     * @test
     */
    public function it_should_not_find_the_projection()
    {
        $projector = $this->createAProjector();

        $projectionManager = $this->createAProjectionManager(ProjectionStatus::IDLE());
        $projectionManagersLocator = $this->createAProjectionManagersLocator($projectionManager);
        $projectionFactory = $this->createAProjectionFactory($projector);

        $projectionName = 'projection that does not exist';
        $projectionFactory->expects($this->any())
            ->method('createByName')
            ->willThrowException(ProjectionNotFound::withName($projectionName));

        $cleaningStrategy = $this->createASut($projectionManagersLocator, $projectionFactory);

        $this->expectException(ProjectionNotFound::class);
        $this->expectExceptionMessage(\sprintf(
            'A projection with name "%s" could not be found.',
            $projectionName
        ));

        $cleaningStrategy->clean($projectionName);
    }

    /**
     * @test
     */
    public function it_should_not_find_the_projection_manager()
    {
        $projector = $this->createAProjector();

        $projectionManager = $this->createAProjectionManager(ProjectionStatus::IDLE());
        $projectionManagersLocator = $this->createAProjectionManagersLocator($projectionManager);
        $projectionFactory = $this->createAProjectionFactory($projector);

        $cleaningStrategy = $this->createASut($projectionManagersLocator, $projectionFactory);

        $projectionManagersLocator->expects($this->any())
            ->method('get')
            ->willThrowException(new Exception('That does not matter'));

        $this->expectException(CleaningProjectionFailed::class);

        $cleaningStrategy->clean('a projection');
    }

    public function provideProjectors(): array
    {
        return [
            'normal projector' => ['projection', $this->createAProjector()],
            'read model projector' => ['read model projection', $this->createAReadModelProjector()],
        ];
    }

    private function createAProjectionManager(ProjectionStatus $status): MockObject
    {
        $projectionManager = $this->getMockForAbstractClass(ProjectionManager::class);

        $projectionManager->expects($this->any())
            ->method('fetchProjectionStatus')
            ->willReturn($status);

        return $projectionManager;
    }

    private function createAProjectionManagersLocator(ProjectionManager $projectionManager): MockObject
    {
        $projectionManagersLocator = $this->createMock(ServiceLocator::class);

        $projectionManagersLocator->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $projectionManagersLocator->expects($this->any())
            ->method('get')
            ->willReturn($projectionManager);

        return $projectionManagersLocator;
    }

    private function createAProjector(): MockObject
    {
        return $this->getMockForAbstractClass(Projector::class);
    }

    private function createAReadModelProjector(): MockObject
    {
        return $this->getMockForAbstractClass(ReadModelProjector::class);
    }

    private function createAProjectionFactory($projector): MockObject
    {
        $projectionFactory = $this->createMock(ProjectionFactory::class);

        $projectionFactory->expects($this->any())
            ->method('createByName')
            ->willReturn($projector);

        return $projectionFactory;
    }

    private function createASut(
        MockObject $projectionManagersLocator,
        MockObject $projectionFactory
    ): PdoCleaningProjectionStrategy {
        return new PdoCleaningProjectionStrategy(
            $projectionManagersLocator,
            $projectionFactory
        );
    }
}
