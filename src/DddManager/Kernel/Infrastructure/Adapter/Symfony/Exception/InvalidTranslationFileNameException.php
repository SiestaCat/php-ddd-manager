<?php declare(strict_types = 1);

namespace Siestacat\DddManager\Kernel\Infrastructure\Adapter\Symfony\Exception;

use RuntimeException;

final class InvalidTranslationFileNameException extends RuntimeException
{
    public function __construct(string $filename, string $expected_name)
    {
        parent::__construct(sprintf('Translation file %s have invalid name, must be "messages.LOCALE.EXT" or same name with current bounded context "%s.LOCALE.EXT"', $filename, $expected_name));
    }
}