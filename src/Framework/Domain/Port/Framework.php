<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Domain\Port;

use Siestacat\DddManager\BoundedContexts\Domain\BoundedContexts;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernelFactory;
use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\SymfonyConsoleApplicationFactory;

interface Framework
{
    public function bounded_contexts():BoundedContexts;

    public static function getName():string;

    public function getKernelFactoryClassName():string;

    public function getKernelFactory():FrameworkKernelFactory;

    public function getConsoleApplicationFactoryClassName():string;

    public function getConsoleApplicationFactory():SymfonyConsoleApplicationFactory;

    public function setBoundedContexts(BoundedContexts $bounded_contexts):void;
}