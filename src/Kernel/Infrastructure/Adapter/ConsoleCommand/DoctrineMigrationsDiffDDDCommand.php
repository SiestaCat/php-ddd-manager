<?php declare(strict_types=1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\ConsoleCommand;

use Siestacat\DddManager\BoundedContexts\Domain\BoundedContext;
use Siestacat\DddManager\Kernel\Domain\Port\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @framework Symfony & Doctrine ORM
 */
#[AsCommand(
    name: 'doctrine:migrations:diff:ddd',
    description: 'Generate migrations for all DDD bounded contexts or a specific bounded context'
)]
class DoctrineMigrationsDiffDDDCommand extends Command
{
    private array $contextTables = [];

    public function __construct
    (
        private readonly Kernel $kernel,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'context',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Generate migration for a specific context only'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be executed without actually running the commands'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $contextFilter = $input->getOption('context');
        $dryRun = $input->getOption('dry-run');

        try {
            // Initialize context-tables mapping
            $this->getAllTables();

            $createdMigrations = [];

            foreach ($this->kernel->bounded_contexts() as $context) {
                // Skip context if context filter is specified and doesn't match
                if ($contextFilter && $context->full_name() !== $contextFilter) {
                    continue;
                }

                $tables = $this->getTables($context);

                // Skip context if no tables found
                if (empty($tables)) {
                    if ($output->isVeryVerbose()) {
                        $io->note(sprintf('Skipping context "%s" - no entities found', $context->full_name()));
                    }
                    continue;
                }

                $tableFilter = $this->generateTableFilter($tables);

                $namespace = $context->namespace() . '\\Infrastructure\\Framework\\Doctrine\\Orm\\Migrations';

                $command = [
                    'php',
                    'bin/console',
                    'doctrine:migrations:diff',
                    '--namespace=' . $namespace,
                    '--filter-expression=' . $tableFilter,
                    '--no-interaction'
                ];

                // Pass the same verbosity level to doctrine:migrations:diff
                if ($output->isVeryVerbose()) {
                    $command[] = '-vvv';
                } elseif ($output->isVerbose()) {
                    $command[] = '-vv';
                } elseif ($output->isDebug()) {
                    $command[] = '-v';
                }

                if ($dryRun) {
                    $io->text('Would execute: ' . implode(' ', $command));
                    continue;
                }

                // Show processing info in verbose mode
                if ($output->isVeryVerbose()) {
                    $io->section(sprintf('Processing context: %s', $context->full_name()));
                    $io->text(sprintf('Generating migration for namespace: %s', $namespace));
                    $io->text(sprintf('Table filter: %s', $tableFilter));
                }

                $process = new Process($command, getcwd());
                $process->setTimeout(300); // 5 minutes timeout
                $process->run();

                if ($process->isSuccessful()) {
                    $processOutput = $process->getOutput();

                    // Show full process output in very verbose mode
                    if ($output->isVeryVerbose()) {
                        $io->success(sprintf('Migration generated successfully for context: %s', $context->full_name()));
                        if ($processOutput) {
                            $io->text($processOutput);
                        }
                        $io->newLine();
                    }

                    // Extract migration file path from output
                    if (preg_match('/Generated new migration class to "(.+)"/', $processOutput, $matches)) {
                        $migrationPath = str_replace(getcwd() . '/', '', $matches[1]);
                        $createdMigrations[] = $migrationPath;
                    }
                } else {
                    $io->error(sprintf('Failed to generate migration for context: %s', $context->full_name()));
                    $io->text($process->getErrorOutput());
                }

                // Add 1 second delay to avoid same timestamp class name for different contexts
                sleep(1);
            }

            // Show results with same style as make:migration
            if (!$dryRun && !empty($createdMigrations)) {
                foreach ($createdMigrations as $migration) {
                    $io->comment('<fg=blue>created</>: ' . $migration);
                }

                $this->writeSuccessMessage($io);

                $io->text([
                    'Review the new migrations then run them with <info>php bin/console doctrine:migrations:migrate</info>',
                    'See <fg=yellow>https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html</>',
                ]);
            } elseif (!$dryRun) {
                $io->warning([
                    'No database changes were detected.',
                ]);
                $io->text([
                    'The database schema and the application mapping information are already in sync.',
                    '',
                ]);
            }

        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Initialize context-tables mapping for all contexts
     */
    private function getAllTables(): void
    {
        if (!empty($this->contextTables)) {
            return; // Already initialized
        }

        $allMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Initialize empty arrays for all contexts
        foreach ($this->kernel->bounded_contexts() as $context) {
            $this->contextTables[$context->full_name()] = [];
        }

        /** @var ClassMetadata $metadata */
        foreach ($allMetadata as $metadata) {
            $entityClass = $metadata->getName();

            // Find which context this entity belongs to
            foreach ($this->kernel->bounded_contexts() as $context) {
                $contextEntityNamespace = $context->namespace() . '\\Domain\\Entity';
                if (strpos($entityClass, $contextEntityNamespace) === 0) {
                    $tableName = $metadata->getTableName();
                    $this->contextTables[$context->full_name()][] = $tableName;
                    break; // Entity found in this context, no need to check others
                }
            }
        }
    }

    /**
     * Get table names for context entities
     */
    private function getTables(BoundedContext $context): array
    {
        return $this->contextTables[$context->full_name()];
    }

    /**
     * Generate table filter expression for given tables
     */
    private function generateTableFilter(array $tables): string
    {
        $quotedTables = array_map(fn($table) => preg_quote($table, '/'), $tables);

        // Create exact match filter (case sensitive)
        return '/^(' . implode('|', $quotedTables) . ')$/';
    }

    private function writeSuccessMessage(SymfonyStyle $io): void
    {
        $io->newLine();
        $io->success('Success!');
        $io->newLine();
    }
}