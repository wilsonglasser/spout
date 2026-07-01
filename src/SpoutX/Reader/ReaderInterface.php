<?php

declare(strict_types=1);

namespace SpoutX\Reader;

/**
 * Interface ReaderInterface
 */
interface ReaderInterface
{
    /**
     * Prepares the reader to read the given file. It also makes sure
     * that the file exists and is readable.
     *
     * @param  string $filePath Path of the file to be read
     * @throws \SpoutX\Common\Exception\IOException
     */
    public function open(string $filePath): void;

    /**
     * Returns an iterator to iterate over sheets.
     *
     * @throws \SpoutX\Reader\Exception\ReaderNotOpenedException If called before opening the reader
     * @return \Iterator To iterate over sheets
     */
    public function getSheetIterator(): \Iterator;

    /**
     * Closes the reader, preventing any additional reading
     */
    public function close(): void;
}
