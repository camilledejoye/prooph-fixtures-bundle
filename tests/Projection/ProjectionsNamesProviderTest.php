<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Tests\Projection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prooph\Bundle\Fixtures\Projection\ProjectionsNamesProvider;
use Prooph\EventStore\Projection\ProjectionManager;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ProjectionsNamesProviderTest extends TestCase
{
    const BATCH_SIZE = 2;

    /**
     * @test
     */
    public function it_gets_all_the_projections_names()
    {
        $projectionsNames = [
            'first projection',
            'second projection',
            'third projection',
            'fourth projection',
        ];

        $projectionManagers = [
            'a projection manager' => $this->createAProjectionManagerFor(
                \array_slice($projectionsNames, 0, 3)
            ),
            'another projection manager' => $this->createAProjectionManagerFor([
                \end($projectionsNames),
            ]),
        ];

        $projectionsNamesrovider = new ProjectionsNamesProvider(
            $this->createAServiceLocatorFor($projectionManagers),
            $this->createProjectionsManagersNames($projectionManagers),
            self::BATCH_SIZE
        );

        $this->assertSame(
            $projectionsNames,
            $projectionsNamesrovider->getNames()
        );
    }

    /**
     * @test
     */
    public function it_loads_the_names_only_once()
    {
        $projectionsNames = [
            'first projection',
            'second projection',
        ];

        $projectionManagers = [
            'a projection manager' => $this->createAProjectionManagerFor($projectionsNames),
        ];

        $projectionManagersLocator = $this->createAServiceLocatorFor($projectionManagers);

        $projectionsNamesrovider = new ProjectionsNamesProvider(
            $projectionManagersLocator,
            $this->createProjectionsManagersNames($projectionManagers)
        );

        $projectionManagersLocator->expects($this->once())
            ->method('get');

        $projectionsNamesrovider->getNames();
        $projectionsNamesrovider->getNames();
    }

    private function createAServiceLocatorFor(array $services): MockObject
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

    private function createAProjectionManagerFor(array $array): MockObject
    {
        $projectionManager = $this->getMockForAbstractClass(ProjectionManager::class);

        $projectionManager->expects($this->any())
            ->method('fetchProjectionNames')
            ->willReturnOnConsecutiveCalls(...\array_merge(
                \array_chunk($array, self::BATCH_SIZE),
                [[]]
            ));

        return $projectionManager;
    }

    private function createProjectionsManagersNames(array $projectionManagers): array
    {
        $projectionsManagersNames = [];
        foreach ($projectionManagers as $projectionManagerName => $projectionManager) {
            $projectionsManagersNames[$projectionManagerName] = 'vendor.'. $projectionManagerName;
        }

        return $projectionsManagersNames;
    }
}
