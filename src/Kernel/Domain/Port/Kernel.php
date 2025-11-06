<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Domain\Port;

use Siestacat\DddManager\BoundedContexts\Domain\BoundedContexts;

interface Kernel
{
    public BoundedContexts $bounded_contexts { get; }

    public string $project_dir { get; }

    public function callFrameworkKernel():FrameworkKernel;

    public function getEnvVars():array;

    public function callFrameworkConsoleApplication():FrameworkConsoleApplication;

    public function getPsr4Namespace(string $srcDirName):string;
}