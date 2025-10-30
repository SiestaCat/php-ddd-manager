<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony;

use Siestacat\DddManager\Kernel\Domain\Port\FrameworkConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * @framework Symfony
 */
class SymfonyConsoleApplication extends Application implements FrameworkConsoleApplication
{
    final public function __construct(SymfonyKernel $kernel)
    {
        parent::__construct($kernel);
    }
}