<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Domain\Port;

use Siestacat\DddManager\Framework\Domain\Port\Framework;

interface FrameworkKernelFactory
{
    public function factory(Framework $framework, Kernel $core_kernel):FrameworkKernel;
}