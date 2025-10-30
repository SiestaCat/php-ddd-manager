<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Domain\Exception;

use DomainException;

final class FrameworkNotExistsException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Framework %s not exists', $name));
    }
}