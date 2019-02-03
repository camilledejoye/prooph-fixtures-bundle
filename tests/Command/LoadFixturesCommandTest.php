<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Tests\Command;

use DummyBundle\DataFixtures\AFixture;
use DummyBundle\DataFixtures\AnotherFixture;
use DummyBundle\DataFixtures\FailingFixture;
use Exception;
use PHPUnit\Framework\TestCase;
use Prooph\Bundle\Fixtures\Command\LoadFixturesCommand;
use Prooph\Bundle\Fixtures\Tests\ProophFixturesTestingKernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoadFixturesCommandTest extends TestCase
{
    const EXIT_OK = 0;
    const EXIT_LOCK = 1;
    const EXIT_NO_FIXTURES = 2;

    /**
     * @test
     */
    public function it_advertises_that_there_is_no_fixtures_and_stop()
    {
        $commandTester = $this->runCommandTester();

        $this->assertContains(
            'There is no fixtures defined!',
            $commandTester->getDisplay()
        );
        $this->assertSame(self::EXIT_NO_FIXTURES, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_not_be_run_while_another_instance_is_running()
    {
        $fakeLocker = new class() {
            use LockableTrait;

            public function __construct()
            {
                $this->lock(LoadFixturesCommand::NAME);
            }

            public function __destruct()
            {
                $this->release();
            }
        };

        $commandTester = $this->runCommandTester();

        $this->assertContains(
            'The command is already running in another process.',
            $commandTester->getDisplay()
        );
        $this->assertSame(self::EXIT_LOCK, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_ask_confirmation_before_doing_anything()
    {
        $registerServices = function (ContainerBuilder $container) {
            $this->registerFixtures($container, [AFixture::class, AnotherFixture::class]);
        };

        $commandTester = $this->runCommandTester($registerServices, ['n']);

        $this->assertContains(
            'The desaster was avoided!',
            $commandTester->getDisplay()
        );
        $this->assertSame(self::EXIT_OK, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_loads_the_fixtures()
    {
        $registerServices = function (ContainerBuilder $container) {
            $this->registerFixtures($container, [AFixture::class, AnotherFixture::class]);
        };

        $commandTester = $this->runCommandTester($registerServices, ['y']);

        $this->assertContains(
            'Loading fixture AFixture: OK',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'Loading fixture AnotherFixture: OK',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'Fixtures loaded without errors!',
            $commandTester->getDisplay()
        );
        $this->assertSame(self::EXIT_OK, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_shows_an_error_when_an_exception_append()
    {
        $registerServices = function (ContainerBuilder $container) {
            $this->registerFixtures($container, [AFixture::class, FailingFixture::class]);
        };

        $commandTester = $this->runCommandTester($registerServices, ['y']);

        $this->assertContains(
            'Loading fixture AFixture: OK',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'Loading fixture FailingFixture: KO',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'In FailingFixture.php line 24:',
            $commandTester->getDisplay()
        );
        $this->assertContains(
            'Something went wrong!',
            $commandTester->getDisplay()
        );
        $this->assertNull($commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_ask_confirmation_in_noninteractive_mode()
    {
        $registerServices = function (ContainerBuilder $container) {
            $this->registerFixtures($container, [AFixture::class, AnotherFixture::class]);
        };

        $commandTester = $this->runCommandTester($registerServices, [], ['interactive' => false]);

        $this->assertContains(
            'Fixtures loaded without errors!',
            $commandTester->getDisplay()
        );
        $this->assertSame(self::EXIT_OK, $commandTester->getStatusCode());
    }

    private function registerFixtures(ContainerBuilder $container, iterable $fixturesFqn): void
    {
        foreach ($fixturesFqn as $fixtureFqn) {
            $container->autowire($fixtureFqn)
                ->setAutoconfigured(true);
        }
    }

    private function runCommandTester(
        callable $registerServices = null,
        array $inputs = [],
        array $options = []
    ): CommandTester {
        $kernel = new ProophFixturesTestingKernel('test', true);

        if (\is_callable($registerServices)) {
            $kernel->registerServices($registerServices);
        }

        $kernel->boot();

        $command = $kernel->getContainer()->get('test.command.load_fixtures');

        $application = new Application('test', 'test');
        $application->add($command);

        $commandTester = new CommandTester($command);

        if ($inputs) {
            $commandTester->setInputs($inputs);
        }

        try {
            $commandTester->execute(
                ['command' => $command->getName()],
                $options
            );
        } catch (Exception $exception) {
            $application->renderException($exception, $commandTester->getOutput());
        }

        return $commandTester;
    }
}
