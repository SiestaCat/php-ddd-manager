<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\Exception;

use RuntimeException;

final class DoctrineTypeAlreadyExistsException extends RuntimeException
{
    public function __construct(string $type_name)
    {
        parent::__construct(sprintf('Doctrine Type name "%s" already defined. Check duplicates on doctrine.yaml or on types.yaml of the bounded contexts', $type_name));
    }
}