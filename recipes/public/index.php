<?php declare(strict_types = 1);

use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Kernel;

/** @framework This is part of Symfony framework, symfony/runtime composer plugin */
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context)
{
    return
    (new Kernel($context))->callFrameworkKernel();
};