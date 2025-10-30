<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Framework\Domain\Exception;

use DomainException;

final class FrameworkNotImplementConsoleApplicationException extends DomainException
{
    public function __construct(string $framework_name)
    {
        parent::__construct(sprintf('Framework %s does not implement console application', $framework_name));
    }
}