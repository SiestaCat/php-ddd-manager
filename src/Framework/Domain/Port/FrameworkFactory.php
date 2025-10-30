<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Domain\Port;

use Siestacat\DddManager\Framework\Domain\Port\Framework;

interface FrameworkFactory
{
    public function factory(string $class_name):Framework;
}