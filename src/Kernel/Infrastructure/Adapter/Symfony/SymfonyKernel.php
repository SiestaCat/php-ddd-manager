<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony;

use Siestacat\DddManager\Framework\Infrastructure\Adapter\Symfony\SymfonyFramework;
use Siestacat\DddManager\Kernel\Domain\Port\FrameworkKernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use DirectoryIterator;
use Siestacat\DddManager\Kernel\Domain\Port\Kernel;
use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\Exception\InvalidTranslationFileNameException;
use Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\Exception\TranslationFileAlreadyExistsException;

/**
 * @framework Symfony
 */
class SymfonyKernel extends BaseKernel implements FrameworkKernel
{
    use MicroKernelTrait;

    final public function __construct
    (
        private readonly SymfonyFramework $framework,
        private readonly Kernel $core_kernel,
        string $environment,
        bool $debug,
        private ?string $project_dir = null
    )
    {
        parent::__construct($environment, $debug);
    }

    final public function getProjectDir():string
    {
        return $this->project_dir?:parent::getProjectDir();
    }

    final public function registerBundles(): iterable
    {
        foreach($this->framework->getBundles() as $bundles)
        {
            foreach($bundles as $class => $envs)
            {
                if ($envs[$this->environment] ?? $envs['all'] ?? false)
                {
                    yield new $class();
                }
            }
        }
    }

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder):void
    {
        foreach($this->framework->bounded_contexts as $bounded_context)
        {
            $configDir = $bounded_context->getConfigPathFramework(SymfonyFramework::getName());

            $container->import($configDir.'/{packages}/*.{php,yaml}');
            $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');

            if(is_dir($configDir.'/services/'))
            {
                $container->import($configDir.'/services/'.'/*.{php,yaml}', null, 'not_found');
            }

            if(is_dir($configDir.'/services/'.$this->environment))
            {
                $container->import($configDir.'/services/'.$this->environment.'/*.{php,yaml}', null, 'not_found');
            }
            

            if (is_file($configDir.'/services.yaml')) {
                $container->import($configDir.'/services.yaml');
                $container->import($configDir.'/{services}_'.$this->environment.'.yaml');
            } else {
                $container->import($configDir.'/{services}.php');
                $container->import($configDir.'/{services}_'.$this->environment.'.php');
            }
        }

        if(class_exists('Symfony\Component\Translation\Translator'))
        {
            $this->addPathsToTranslator($builder);
        }

        if($builder->hasExtension('twig'))
        {
            $this->addPathsToTwig($builder);
        }

        if($builder->hasExtension('doctrine'))
        {
            $this->addMappingsToDoctrine($builder);
        }

        if($builder->hasExtension('doctrine_migrations'))
        {
            $this->addDoctrineMigrations($builder);
        }

        // Register core kernel service
        $this->registerCoreKernelService($builder);

        // Register console commands
        $this->registerConsoleCommands($builder);
    }

    private function configureRoutes(RoutingConfigurator $routes):void
    {
        foreach($this->framework->bounded_contexts as $bounded_context)
        {
            $configDir = $bounded_context->getConfigPathFramework(SymfonyFramework::getName());

            $routes->import($configDir.'/{routes}/'.$this->environment.'/*.{php,yaml}');
            $routes->import($configDir.'/{routes}/*.{php,yaml}');

            if (is_file($configDir.'/routes.yaml')) {
                $routes->import($configDir.'/routes.yaml');
            } else {
                $routes->import($configDir.'/{routes}.php');
            }

            if (false !== ($fileName = (new \ReflectionObject($this))->getFileName())) {
                $routes->import($fileName, 'attribute');
            }
        }
    }

    private function addPathsToTwig(ContainerBuilder $builder):void
    {
        $paths = [];
        foreach($this->framework->bounded_contexts as $bounded_context)
        {
            $templatesDir = $bounded_context->getSubPathFramework(SymfonyFramework::getName(), 'templates');

            if($templatesDir && is_dir($templatesDir))
            {
                $paths[$templatesDir] = $bounded_context->full_name_snake;
            }
        }

        if(!empty($paths))
        {
            $existingConfig = $builder->getExtensionConfig('twig');
            $mergedPaths = [];

            // Merge existing paths from config
            foreach($existingConfig as $config)
            {
                if(isset($config['paths']))
                {
                    $mergedPaths = array_merge($mergedPaths, $config['paths']);
                }
            }

            // Add our new paths
            $mergedPaths = array_merge($mergedPaths, $paths);

            $builder->loadFromExtension('twig', [
                'paths' => $mergedPaths
            ]);
        }
    }

    private function addPathsToTranslator(ContainerBuilder $builder):void
    {
        $unique_files = [];
        $paths = [];

        foreach($this->framework->bounded_contexts as $bounded_context)
        {
            $translationsDir = $bounded_context->getSubPathFramework(SymfonyFramework::getName(), 'translations');

            if($translationsDir && is_dir($translationsDir))
            {
                foreach(new DirectoryIterator($translationsDir) as $fileInfo)
                {
                    if(!in_array($fileInfo->getExtension(), $this->getTranslationAllowedExtensions()))
                    {
                        continue;
                    }

                    $filename_split = explode('.', $fileInfo->getFilename());

                    if(count($filename_split) < 3)
                    {
                        continue;
                    }

                    if(array_key_exists($fileInfo->getFilename(), $unique_files))
                    {
                        throw new TranslationFileAlreadyExistsException($fileInfo->getFilename(), $unique_files[$fileInfo->getFilename()]);
                    }

                    $domain_name = join('.', array_slice($filename_split, 0, -2));

                    if($domain_name <> 'messages' && $domain_name <> $bounded_context->full_name_snake_dot)
                    {
                        throw new InvalidTranslationFileNameException($fileInfo->getFilename(), $bounded_context->full_name_snake_dot);
                    }

                    $unique_files[$fileInfo->getFilename()] = $fileInfo->getPath();
                }
                $paths[] = $translationsDir;
            }
        }

        if(!empty($paths))
        {
            $existingConfig = $builder->getExtensionConfig('framework');
            $mergedPaths = [];

            // Merge existing translator paths from config
            foreach($existingConfig as $config)
            {
                if(isset($config['translator']['paths']))
                {
                    $mergedPaths = array_merge($mergedPaths, $config['translator']['paths']);
                }
            }

            // Add our new paths
            $mergedPaths = array_merge($mergedPaths, $paths);

            $builder->loadFromExtension('framework', [
                'translator' => [
                    'paths' => $mergedPaths
                ]
            ]);
        }
    }

    private function addMappingsToDoctrine(ContainerBuilder $builder):void
    {
        $mappings = [];

        foreach($this->framework->bounded_contexts as $bounded_context)
        {
            $entityDir = $bounded_context->getSubPath('/Infrastructure/Framework/Doctrine/Orm/Mapping');

            if($entityDir && is_dir($entityDir))
            {
                $mappings[$bounded_context->full_name_snake] = [
                    'is_bundle' => false,
                    'type' => 'xml',
                    'dir' => $entityDir,
                    'prefix' => $bounded_context->namespace . '\\Domain\\Entity',
                    'alias' => $bounded_context->full_name
                ];
            }
        }

        if(!empty($mappings))
        {
            $existingConfig = $builder->getExtensionConfig('doctrine');
            $mergedMappings = [];

            // Merge existing mappings from config
            foreach($existingConfig as $config)
            {
                if(isset($config['orm']['mappings']))
                {
                    $mergedMappings = array_merge($mergedMappings, $config['orm']['mappings']);
                }
            }

            // Add our new mappings
            $mergedMappings = array_merge($mergedMappings, $mappings);

            $builder->loadFromExtension('doctrine', [
                'orm' => [
                    'mappings' => $mergedMappings
                ]
            ]);
        }
    }

    private function addDoctrineMigrations(ContainerBuilder $builder):void
    {
        $migrationsPaths = [];

        foreach($this->framework->bounded_contexts as $bounded_context)
        {
            $migrationsDir = $bounded_context->getSubPath('/Infrastructure/Framework/Doctrine/Migrations', false);

            if(!is_dir($migrationsDir))
            {
                mkdir($migrationsDir, 0755, true);
                touch($migrationsDir . '/.gitignore');
            }
            $migrationsPaths[$bounded_context->namespace . '\\Infrastructure\\Framework\\Doctrine\\Orm\\Migrations'] = $migrationsDir;
        }

        if(!empty($migrationsPaths))
        {
            $existingConfig = $builder->getExtensionConfig('doctrine_migrations');
            $mergedPaths = [];

            // Merge existing migrations paths from config
            foreach($existingConfig as $config)
            {
                if(isset($config['migrations_paths']))
                {
                    $mergedPaths = array_merge($mergedPaths, $config['migrations_paths']);
                }
            }

            // Add our new migrations paths
            $mergedPaths = array_merge($mergedPaths, $migrationsPaths);

            //dd($mergedPaths);

            $builder->loadFromExtension('doctrine_migrations', [
                'migrations_paths' => $mergedPaths,
                'enable_profiler' => false
            ]);
        }
    }

    private function getTranslationAllowedExtensions():array
    {
        return [
            'php',
            'yml',
            'yaml',
            'xlf',
            'xliff',
            'po',
            'mo',
            'ts',
            'csv',
            'res',
            'dat',
            'ini',
            'json'
        ];
    }

    private function registerCoreKernelService(ContainerBuilder $builder):void
    {
        // Register core_kernel using the kernel service reference
        $coreKernelDefinition = new Definition(get_class($this->core_kernel));
        $coreKernelDefinition->setFactory([new Reference('kernel'), 'getCoreKernel']);
        $builder->setDefinition('app.core_kernel', $coreKernelDefinition);
    }

    private function registerConsoleCommands(ContainerBuilder $builder):void
    {
        // Register DoctrineMigrationsDiffDDDCommand with core kernel and entity manager injection
        $builder->register('app.doctrine_migrations_diff_ddd_command', 'Siestacat\DddManager\Kernel\Infrastructure\Adapter\ConsoleCommand\DoctrineMigrationsDiffDDDCommand')
            ->addArgument(new Reference('app.core_kernel'))
            ->addArgument(new Reference('doctrine.orm.entity_manager'))
            ->addTag('console.command');
    }

    public function getCoreKernel():Kernel
    {
        return $this->core_kernel;
    }
}