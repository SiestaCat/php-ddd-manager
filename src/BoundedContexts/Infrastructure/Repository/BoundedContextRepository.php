<?php declare(strict_types = 1);

namespace Siestacat\DddManager\BoundedContexts\Infrastructure\Repository;

use DirectoryIterator;
use Siestacat\DddManager\BoundedContexts\Domain\BoundedContext;
use Siestacat\DddManager\BoundedContexts\Domain\BoundedContexts;
use Siestacat\DddManager\BoundedContexts\Domain\Exception\NotBoundedContextFoundOnPathException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @framework Symfony
 */
class BoundedContextRepository
{
    private BoundedContexts $bounded_contexts;

    final public function __construct
    (
        private readonly string $base_abs_path,
        private readonly string $base_namespace
    )
    {}

    public function findAll():BoundedContexts
    {
        if(!isset($this->bounded_contexts))
        {
            $bounded_contexts = [];

            /** @var DirectoryIterator|RecursiveIteratorIterator|RecursiveDirectoryIterator */
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->base_abs_path));
            $it->rewind();
            while($it->valid())
            {
                if (!$it->isDot() && basename($it->key()) === '.dddcontext')
                {
                    $bounded_contexts[] =
                    new BoundedContext
                    (
                        abs_path: dirname($it->key()),
                        base_path: $this->base_abs_path,
                        base_namespace: $this->base_namespace
                    );
                }
                $it->next();
            }

            $this->bounded_contexts = new BoundedContexts($bounded_contexts);
        }
        
        return $this->bounded_contexts;
    }

    public function getBoundedContextByPath(string $current_path):BoundedContext
    {
        $current_path = realpath($current_path);

        $current_path = is_dir($current_path) ? $current_path : dirname($current_path);

        foreach ($this->bounded_contexts as $bounded_context)
        {
            if (str_starts_with($current_path, $bounded_context->abs_path))
            {
                return $bounded_context;
            }
        }

        throw new NotBoundedContextFoundOnPathException($current_path);
    }
}