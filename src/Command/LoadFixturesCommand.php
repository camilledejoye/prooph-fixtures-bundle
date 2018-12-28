<?php

/**
 * This file is part of elythy/prooph-fixtures-bundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\Bundle\Fixtures\Command;

use Prooph\Fixtures\FixturesManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class LoadFixturesCommand extends Command
{
    use LockableTrait;

    const NAME = 'event-store:fixtures:load';
    const EXIT_OK = 0;
    const EXIT_LOCK = 1;
    const EXIT_NO_FIXTURES = 2;

    protected static $defaultName = self::NAME;

    /**
     * @var FixturesManager
     */
    private $fixturesManager;

    /**
     * Creates the command loading the fixtures.
     *
     * @param ProophFixturesManager $fixturesManager
     */
    public function __construct(FixturesManager $fixturesManager)
    {
        parent::__construct();

        $this->fixturesManager = $fixturesManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Loads the fixtures of the project');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $io->title('Prooph fixtures loading');

            if (! $this->lock(null, false)) {
                $io->error('The command is already running in another process.');

                return self::EXIT_LOCK;
            }

            if (! $fixtures = $this->fixturesManager->getFixtures()) {
                $io->error('There is no fixtures defined!');

                $this->release();

                return self::EXIT_NO_FIXTURES;
            }

            if ($input->isInteractive() && ! $this->advertiseAndAskConformation($io)) {
                $io->comment('The desaster was avoided!');

                $this->release();

                return self::EXIT_OK;
            }

            $this->fixturesManager->cleanUp();

            foreach ($fixtures as $fixture) {
                try {
                    $io->write(\sprintf(
                        ' <comment>></comment> <fg=blue>Loading fixture <comment>%s</comment>:</> ',
                        $fixture->getName()
                    ));
                    $fixture->load();
                    $io->writeln('<fg=green>OK</>');
                } catch (Throwable $error) {
                    $io->writeln('<fg=red>KO</>');
                    throw $error;
                }
            }

            $io->success('Fixtures loaded without errors!');

            $this->release();

            return self::EXIT_OK;
        } catch (Throwable $error) {
            $this->release();
            throw $error;
        }
    }

    /**
     * Advertises that all event streams will be deleted and all projections reseted.
     * Then asks if we should continue and return the answer.
     *
     * @param SymfonyStyle $io
     *
     * @return bool
     */
    private function advertiseAndAskConformation(SymfonyStyle $io): bool
    {
        $continueQuestion = new ConfirmationQuestion(
            'Are you sure you want to continue ?',
            false,
            '/^y|yes$/'
        );
        $io->writeln('<comment>You are about to reset your database !</comment>');

        return $io->askQuestion($continueQuestion);
    }
}
