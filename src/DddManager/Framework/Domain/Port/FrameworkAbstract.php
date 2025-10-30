<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Domain\Port;

use Siestacat\DddManager\BoundedContexts\Domain\BoundedContexts;
use Siestacat\DddManager\Framework\Domain\Exception\FrameworkNotImplementConsoleApplicationException;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernelFactory;
use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\SymfonyConsoleApplicationFactory;

abstract class FrameworkAbstract implements Framework
{
    final public BoundedContexts $bounded_contexts
    {
        get
        {
            return $this->bounded_contexts;
        }
    }

    final public function getKernelFactory():FrameworkKernelFactory
    {
        return new ($this->getKernelFactoryClassName());
    }

    final public function getConsoleApplicationFactory():SymfonyConsoleApplicationFactory
    {
        return new ($this->getConsoleApplicationFactoryClassName());
    }

    public function getConsoleApplicationFactoryClassName():string
    {
        throw new FrameworkNotImplementConsoleApplicationException(self::getName());
    }

    final public function setBoundedContexts(BoundedContexts $bounded_contexts):void
    {
        $this->bounded_contexts = $bounded_contexts;
    }
}