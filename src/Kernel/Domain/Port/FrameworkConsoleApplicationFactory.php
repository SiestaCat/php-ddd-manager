<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Domain\Port;

interface FrameworkConsoleApplicationFactory
{
    public function factory(FrameworkKernel $kernel):FrameworkConsoleApplication;
}