<?php

namespace Prooph\Bundle\Fixtures\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Prooph\Bundle\Fixtures\Tests\Fixtures\AFixture;
use Prooph\Fixtures\Locator\FixturesLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Prooph\Bundle\Fixtures\Tests\ProophFixturesTestingKernel;

class ProophFixturesExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_the_tag_to_the_fixures()
    {
        $container = $this->loadContainerFromServicesRegistration(
            static function (ContainerBuilder $container): void {
                $container->autowire(AFixture::class)
                    ->setAutoconfigured(true)
                    ;
            }
        );

        $fixturesLocator = $this->getFixturesLocator($container);
        $fixtures = $fixturesLocator->getFixtures();

        $this->assertContains(AFixture::class, array_keys($fixtures));
    }

    private function getFixturesLocator(ContainerInterface $container): FixturesLocator
    {
        $fixturesLocator = $container->get(ProophFixturesTestingKernel::FIXTURES_LOCATOR_ID);
        return $fixturesLocator;
    }

    private function loadContainerFromServicesRegistration($registerServices): ContainerInterface
    {
        $kernel = new ProophFixturesTestingKernel('test', true);
        $kernel->registerServices($registerServices);
        $kernel->boot();

        return $kernel->getContainer();
    }
}
