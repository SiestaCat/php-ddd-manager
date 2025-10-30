<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\Exception;

use RuntimeException;

final class TranslationFileAlreadyExistsException extends RuntimeException
{
    public function __construct(string $filename, string $existing_path)
    {
        parent::__construct(sprintf('Translation file %s already exists in another bounded context in path %s', $filename, $existing_path));
    }
}