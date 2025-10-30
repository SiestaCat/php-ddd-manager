<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Infrastructure\Repository;

use Siestacat\DddManager\Framework\Domain\Exception\FrameworkNotExistsException;
use Siestacat\DddManager\Framework\Infrastructure\Adapter\Symfony\SymfonyFramework;

class FrameworkRepository
{
    public function getList():array
    {
        return
        [
            SymfonyFramework::getName() => SymfonyFramework::class
        ];
    }

    public function getClassNameByName(string $name):string
    {
        $list = $this->getList();
        if(array_key_exists($name,$list))
        {
            return $list[$name];
        }

        throw new FrameworkNotExistsException($name);
    }
}