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
use PHPUnit\Framework\TestCase;
use Prooph\Bundle\Fixtures\Command\LoadFixturesCommand;
use Prooph\Bundle\Fixtures\Tests\ProophFixturesTestingKernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoadFixturesCommandTest extends TestCase
{
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
        $this->assertSame(2, $commandTester->getStatusCode());
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
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_ask_confirmation_before_doing_anything()
    {
        $commandTester = $this->runCommandTester(true, ['n']);

        $this->assertContains(
            'The desaster was avoided!',
            $commandTester->getDisplay()
        );
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_loads_the_fixtures()
    {
        $commandTester = $this->runCommandTester(true, ['y']);

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
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @test
     */
    public function it_does_not_ask_confirmation_in_noninteractive_mode()
    {
        $commandTester = $this->runCommandTester(true, [], ['interactive' => false]);

        $this->assertContains(
            'Fixtures loaded without errors!',
            $commandTester->getDisplay()
        );
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    private function runCommandTester(
        bool $registerFixtures = false,
        array $inputs = [],
        array $options = []
    ): CommandTester {
        $kernel = new ProophFixturesTestingKernel('test', true);
        $kernel->registerServices(
            static function (ContainerBuilder $container) use ($registerFixtures) {
                $container->setAlias(
                    'test.command.load_fixtures',
                    new Alias('Prooph\Bundle\Fixtures\Command\LoadFixturesCommand', true)
                );

                if ($registerFixtures) {
                    $container->autowire(AFixture::class)
                        ->setAutoconfigured(true);

                    $container->autowire(AnotherFixture::class)
                        ->setAutoconfigured(true);
                }
            }
        );
        $kernel->boot();

        $command = $kernel->getContainer()->get('test.command.load_fixtures');

        $application = new Application('test', 'test');
        $application->add($command);

        $commandTester = new CommandTester($command);

        if ($inputs) {
            $commandTester->setInputs($inputs);
        }

        $commandTester->execute(
            ['command' => $command->getName()],
            $options
        );

        return $commandTester;
    }
}
