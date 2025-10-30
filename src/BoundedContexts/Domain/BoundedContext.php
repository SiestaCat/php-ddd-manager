<?php declare(strict_types = 1);

namespace Siestacat\DddManager\BoundedContexts\Domain;

final class BoundedContext
{
    private string $base_namespace;

    public function __construct
    (
        public readonly string $abs_path,
        private readonly string $base_path,
        string $base_namespace
    )
    {
        $this->base_namespace = rtrim($base_namespace, '\\');
    }

    public function short_name():string
    {
        return $this->rel_path_sliced[array_key_last($this->rel_path_sliced())];
    }

    public function full_name():string
    {
        return join('', array_map('ucwords', $this->rel_path_sliced()));
    }

    public function short_name_snake():string
    {
        return mb_strtolower($this->short_name());
    }

    public function full_name_snake():string
    {
        return join('_', array_map('mb_strtolower', $this->rel_path_sliced()));
    }

    public function full_name_snake_dot():string
    {
        return join('.', array_map('mb_strtolower', $this->rel_path_sliced()));
    }

    private function rel_path_sliced():string
    {
        $rel_path = substr($this->abs_path, strlen($this->base_path));
        return explode(DIRECTORY_SEPARATOR, trim($rel_path, DIRECTORY_SEPARATOR));
    }

    public function namespace():string
    {
        return $this->base_namespace . '\\' . join('\\', $this->rel_path_sliced());
    }

    public function getConfigPathFramework(string $framework_name, ?string $subpath = null):?string
    {
        $path = $this->abs_path . '/Infrastructure/Framework/' . mb_ucfirst($framework_name) . '/config' . ($subpath?'/'.$subpath:null);
        return file_exists($path) && is_readable($path) ? $path : null;
    }

    public function getSubPathFramework(string $framework_name, string $subpath):?string
    {
        $path = $this->abs_path . '/Infrastructure/Framework/' . mb_ucfirst($framework_name) . '/' . $subpath;
        return file_exists($path) && is_readable($path) ? $path : null;
    }

    public function getSubPath(string $subpath, bool $return_null_if_not_exists = true):?string
    {
        $path = $this->abs_path . $subpath;
        return file_exists($path) && is_readable($path) ? $path : ($return_null_if_not_exists ? null : $path);
    }
}