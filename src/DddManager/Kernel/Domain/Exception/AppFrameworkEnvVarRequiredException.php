<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Domain\Exception;

use DomainException;

final class AppFrameworkEnvVarRequiredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The APP_FRAMEWORK environment variable is required.');
    }
}