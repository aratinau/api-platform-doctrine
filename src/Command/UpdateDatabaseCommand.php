<?php

namespace App\Command;

use App\Kernel;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:database:update',
    description: 'Updates an existing database.',
    aliases: ['app:update-database'],
    hidden: false
)]
class UpdateDatabaseCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private KernelInterface $kernel
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('databaseName', InputArgument::OPTIONAL, 'The name of the database')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Update all databases')
            ->addOption('dev-force-all', null, InputOption::VALUE_NONE, 'Update all databases with --force on dev')
            ->addOption('load-fixtures', null, InputOption::VALUE_NONE, 'Load fixtures database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('all')) {
            $output->writeln('Updating all databases');

            $dbs = $this->doctrine->getConnection()->getDatabases();
            foreach ($dbs as $db) {
                $kernel = new Kernel(
                    $this->kernel->getEnvironment(),
                    $this->kernel->isDebug()
                );
                $application = new Application($kernel);
                $application->setAutoExit(false);

                $arguments = [
                    'command' => 'app:database:update',
                    'databaseName' => $db
                ];

                $greetInput = new ArrayInput($arguments);
                $application->run($greetInput, $output);
            }
        } else {
            if ($input->getArgument('databaseName')) {
                $output->writeln('Updating ' . $input->getArgument('databaseName') . ' database');
                $this->updateSingleDatabase($input->getArgument('databaseName'), $output);
            }
        }

        if ($input->getOption('dev-force-all')) {
            if ($_ENV['APP_ENV'] === 'dev') {
                $output->writeln('Updating in force mode (dev only) ' . $input->getArgument('databaseName') . ' database');

                $dbs = $this->doctrine->getConnection()->getDatabases();
                foreach ($dbs as $db) {
                    $this->doctrine->getConnection()->changeDatabase($db);

                    $application = new Application($this->kernel);
                    $application->setAutoExit(false);

                    $arguments = [
                        'command' => 'doctrine:schema:update',
                        '--force' => true,
                        '--complete' => true,
                    ];

                    $commandInput = new ArrayInput($arguments);
                    $commandInput->setInteractive(false);
                    $application->run($commandInput, $output);
                }
            }
        }

        if ($input->getOption('load-fixtures')) {
            if ($_ENV['APP_ENV'] === 'dev') {
                $output->writeln('Load fixtures (dev only) ' . $input->getArgument('databaseName') . ' database');

                $dbs = $this->doctrine->getConnection()->getDatabases();
                foreach ($dbs as $db) {
                    $this->doctrine->getConnection()->changeDatabase($db);

                    $application = new Application($this->kernel);
                    $application->setAutoExit(false);

                    $arguments = [
                        'command' => 'doctrine:fixtures:load',
                        '--no-interaction' => true,
                    ];

                    $commandInput = new ArrayInput($arguments);
                    $commandInput->setInteractive(false);
                    $application->run($commandInput, $output);
                }
            }
        }

        return Command::SUCCESS;
    }

    private function updateSingleDatabase(string $databaseName, OutputInterface $output): void
    {
        $this->doctrine->getConnection()->changeDatabase($databaseName);

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $arguments = [
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
            '--no-debug' => true,
            '--allow-no-migration' => true
        ];

        $commandInput = new ArrayInput($arguments);
        $commandInput->setInteractive(false);
        $application->run($commandInput, $output);
    }
}
