<?php

namespace WilsonGlasser\Spout\Reader\Common\Creator;

use WilsonGlasser\Spout\Common\Exception\UnsupportedTypeException;
use WilsonGlasser\Spout\Common\Type;
use WilsonGlasser\Spout\Reader\ReaderInterface;

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
     * @throws \WilsonGlasser\Spout\Common\Exception\UnsupportedTypeException
     * @return ReaderInterface
     */
    public static function createReader($readerType)
    {
        return ReaderFactory::createFromType($readerType);
    }

    /**
     * Creates a reader by file extension
     *
     * @param string $path The path to the spreadsheet file. Supported extensions are .csv, .ods and .xlsx
     * @throws \WilsonGlasser\Spout\Common\Exception\UnsupportedTypeException
     * @return ReaderInterface
     */
    public static function createReaderFromFile($path)
    {
        return ReaderFactory::createFromFile($path);
    }

    /**
     * This creates an instance of a CSV reader
     *
     * @return \WilsonGlasser\Spout\Reader\CSV\Reader
     */
    public static function createCSVReader()
    {
        try {
            return ReaderFactory::createFromType(Type::CSV);
        } catch (UnsupportedTypeException $e) {
            // should never happen
            return null;
        }
    }

    /**
     * This creates an instance of a XLSX reader
     *
     * @return \WilsonGlasser\Spout\Reader\XLSX\Reader
     */
    public static function createXLSXReader()
    {
        try {
            return ReaderFactory::createFromType(Type::XLSX);
        } catch (UnsupportedTypeException $e) {
            // should never happen
            return null;
        }
    }

    /**
     * This creates an instance of a ODS reader
     *
     * @return \WilsonGlasser\Spout\Reader\ODS\Reader
     */
    public static function createODSReader()
    {
        try {
            return ReaderFactory::createFromType(Type::ODS);
        } catch (UnsupportedTypeException $e) {
            // should never happen
            return null;
        }
    }
}
