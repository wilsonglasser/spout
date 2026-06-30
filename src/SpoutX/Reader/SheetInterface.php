<?php

declare(strict_types=1);

namespace SpoutX\Reader;

/**
 * Interface SheetInterface
 */
interface SheetInterface
{
    /**
     * Returns an iterator to iterate over the sheet's rows.
     *
     * @return IteratorInterface
     */
    public function getRowIterator();
}
