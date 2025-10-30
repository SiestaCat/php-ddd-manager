<?php declare(strict_types = 1);

namespace Siestacat\DddManager\BoundedContexts\Domain;

use ArrayIterator;

final class BoundedContexts extends ArrayIterator
{
    final public function __construct(array $bounded_contexts)
    {
        parent::__construct($bounded_contexts);
    }

    /** @return BoundedContext */
    public function current():mixed
    {
        return parent::current();
    }
}