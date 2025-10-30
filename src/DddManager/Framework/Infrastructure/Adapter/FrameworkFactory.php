<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Infrastructure\Adapter;

use Siestacat\DddManager\Framework\Domain\Port\Framework;
use Siestacat\DddManager\Framework\Domain\Port\FrameworkFactory as PortFrameworkFactory;

class FrameworkFactory implements PortFrameworkFactory
{
    public function factory(string $class_name):Framework
    {
        return new $class_name;
    }
}