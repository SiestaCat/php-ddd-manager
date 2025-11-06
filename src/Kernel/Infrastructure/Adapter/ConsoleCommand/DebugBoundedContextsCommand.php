<?php declare(strict_types=1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\ConsoleCommand;

use Siestacat\DddManager\Kernel\Domain\Port\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'debug:ddd:bounded-contexts',
    description: 'Shows a list of all bounded contexts in the application'
)]
class DebugBoundedContextsCommand extends Command
{
    public function __construct
    (
        private readonly Kernel $kernel
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $table_rows = [];

        foreach($this->kernel->bounded_contexts as $bounded_context)
        {
            $table_rows[] =
            [
                $bounded_context->full_name,
                $bounded_context->full_name_snake,
                $bounded_context->full_name_snake_dot,
                $bounded_context->namespace,
                $bounded_context->abs_path
            ];
        }
        
        $table = new Table($output);
        $table
            ->setHeaders
            (
                [
                    'Full Name',
                    'Full Name Snake',
                    'Full Name Snake Dot',
                    'Name Space',
                    'Path'
                ]
            )
            ->setRows($table_rows)
        ;
        $table->render();

        return Command::SUCCESS;
    }
}