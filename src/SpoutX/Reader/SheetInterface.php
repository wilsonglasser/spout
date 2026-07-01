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
     */
    public function getRowIterator(): IteratorInterface;

    /**
     * @return string[] Merge-cell ranges of the sheet, e.g. ["A1:B1", "C7:E7"]
     */
    public function getMergeCells(): array;
}
