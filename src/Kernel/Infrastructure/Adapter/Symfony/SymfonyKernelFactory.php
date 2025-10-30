<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony;

use Siestacat\DddManager\Framework\Domain\Port\Framework;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernelFactory;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernel;
use Siestacat\DddManager\Kernel\Domain\Port\Kernel;

/**
 * @framework Symfony
 */
class SymfonyKernelFactory implements FrameworkKernelFactory
{
    public function factory(Framework $framework, Kernel $core_kernel):FrameworkKernel
    {
        $env_vars = $core_kernel->getEnvVars();
        return new SymfonyKernel($framework, $core_kernel, $env_vars['APP_ENV'], boolval($env_vars['APP_DEBUG']));
    }
}