<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Domain\Port;

interface FrameworkKernel
{
    public function getProjectDir():string;
}