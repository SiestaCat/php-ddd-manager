<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Infrastructure\Adapter\Symfony;

use Siestacat\DddManager\Framework\Domain\Port\FrameworkAbstract;
use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\SymfonyConsoleApplicationFactory;
use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\SymfonyKernelFactory;

/**
 * @framework Symfony
 */
final class SymfonyFramework extends FrameworkAbstract
{
    final public static function getName():string
    {
        return 'symfony';
    }

    final public function getKernelFactoryClassName():string
    {
        return SymfonyKernelFactory::class;
    }

    final public function getConsoleApplicationFactoryClassName():string
    {
        return SymfonyConsoleApplicationFactory::class;
    }

    final public function getBundles():iterable
    {
        foreach($this->bounded_contexts as $bounded_context)
        {
            $bundles_path = $bounded_context->getConfigPathFramework($this->getName(), 'bundles.php');

            if($bundles_path)
            {
                yield require $bundles_path;
            }
        }
    }
}