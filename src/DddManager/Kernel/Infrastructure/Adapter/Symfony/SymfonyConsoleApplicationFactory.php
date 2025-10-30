<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony;

use Siestacat\DddManager\Framework\Domain\Port\Framework;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkConsoleApplication;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkConsoleApplicationFactory;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernel;

/**
 * @framework Symfony
 */
class SymfonyConsoleApplicationFactory implements FrameworkConsoleApplicationFactory
{
    public function factory(FrameworkKernel $kernel):FrameworkConsoleApplication
    {
        return new SymfonyConsoleApplication($kernel);
    }
}