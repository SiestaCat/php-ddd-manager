<?php declare(strict_types = 1);

namespace Siestacat\DddManager\BoundedContexts\Domain\Exception;

use DomainException;

final class NotBoundedContextFoundOnPathException extends DomainException
{
    final public function __construct(string $path)
    {
        parent::__construct("No bounded context found for path: {$path}");
    }
}