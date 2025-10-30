<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter;

use Siestacat\DddManager\BoundedContexts\Domain\BoundedContexts;
use Siestacat\DddManager\BoundedContexts\Infrastructure\Repository\BoundedContextRepository;
use Siestacat\DddManager\Framework\Domain\Port\Framework;
use Siestacat\DddManager\Framework\Infrastructure\Adapter\FrameworkFactory;
use Siestacat\DddManager\Framework\Infrastructure\Repository\FrameworkRepository;
use Siestacat\DddManager\Kernel\Domain\Exception\AppFrameworkEnvVarRequiredException;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkConsoleApplication;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernel;
use Siestacat\DddManager\Kernel\Domain\Port\Kernel as PortKernel;

class Kernel implements PortKernel
{
    private Framework $framework;

    private FrameworkKernel $framework_kernel;

    public BoundedContexts $bounded_contexts
    {
        get
        {
            return $this->bounded_contexts;
        }
    }

    private string $framework_name
    {
        get
        {
            if(!isset($this->env_vars['APP_FRAMEWORK']))
            {
                throw new AppFrameworkEnvVarRequiredException;
            }
            return $this->env_vars['APP_FRAMEWORK'];
        }
    }

    final public function __construct
    (
        private readonly array $env_vars,
        private readonly bool $override_project_dir = true
    )
    {
        $framework_class_name = new FrameworkRepository()->getClassNameByName($this->framework_name);
        $this->framework = new FrameworkFactory()->factory(class_name: $framework_class_name);
        $base_path = rtrim($this->callFrameworkKernel()->getProjectDir()) . '/src/BoundedContexts';
        $psr4_namespace = $this->getPsr4Namespace('src') . 'BoundedContexts';
        $this->bounded_contexts = new BoundedContextRepository($base_path, $psr4_namespace)->findAll();
        $this->framework->setBoundedContexts($this->bounded_contexts);
    }

    public function getPsr4Namespace(string $srcDirName):string
    {
        $loader = require $this->callFrameworkKernel()->getProjectDir() . '/vendor/autoload.php';
        $prefixes = $loader->getPrefixesPsr4();
        $srcPath = realpath($this->callFrameworkKernel()->getProjectDir() . '/' . $srcDirName);

        foreach ($prefixes as $namespace => $paths)
        {
            foreach($paths as $path)
            {
                $realpath = realpath($path);

                if($srcPath === $realpath)
                {
                    return $namespace;
                }
            }
        }

        throw new \RuntimeException("No se pudo encontrar el namespace base para $srcDirName/");
    }

    public function callFrameworkKernel():FrameworkKernel
    {
        if(!isset($this->framework_kernel))
        {
            $this->framework_kernel = $this->framework->getKernelFactory()
            ->factory
            (
                framework: $this->framework,
                core_kernel: $this
            );
        }
        
        return $this->framework_kernel;
    }

    public function callFrameworkConsoleApplication():FrameworkConsoleApplication
    {
        return $this->framework->getConsoleApplicationFactory()->factory($this->callFrameworkKernel());
    }

    public function getEnvVars():array
    {
        return $this->env_vars;
    }

    public function getVendorChildDir():?string
    {
        return
        $this->override_project_dir
        ?
        realpath(__DIR__ . '/../../..') // /app/path/vendor/siestacat/ddd-manager -> /app/path
        :
        null
        ;
    }
}