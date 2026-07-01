<?php

declare(strict_types=1);

namespace SpoutX\Reader\Common\Creator;

use SpoutX\Reader\ReaderInterface;

/**
 * Class ReaderEntityFactory
 * Factory to create external entities
 */
class ReaderEntityFactory
{
    /**
     * This creates an instance of the appropriate reader, given the type of the file to be read
     *
     * @param  string $readerType Type of the reader to instantiate
     * @throws \SpoutX\Common\Exception\UnsupportedTypeException
     * @return ReaderInterface
     */
    public static function createReader(string $readerType): ReaderInterface
    {
        return (new ReaderFactory())->create($readerType);
    }

    /**
     * Creates a reader by file extension
     *
     * @param string The path to the spreadsheet file. Only the .xlsx extension is supported.
     * @throws \SpoutX\Common\Exception\IOException
     * @throws \SpoutX\Common\Exception\UnsupportedTypeException
     * @return ReaderInterface
     */
    public static function createReaderFromFile(string $path): ReaderInterface
    {
        return (new ReaderFactory())->createFromFile($path);
    }
}
