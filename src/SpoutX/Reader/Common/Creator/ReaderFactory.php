<?php

namespace SpoutX\Reader\Common\Creator;

use SpoutX\Common\Exception\IOException;
use SpoutX\Common\Exception\UnsupportedTypeException;
use SpoutX\Common\Type;
use SpoutX\Reader\ReaderInterface;
use SpoutX\Reader\XLSX\Creator\HelperFactory as XLSXHelperFactory;
use SpoutX\Reader\XLSX\Creator\InternalEntityFactory as XLSXInternalEntityFactory;
use SpoutX\Reader\XLSX\Creator\ManagerFactory as XLSXManagerFactory;
use SpoutX\Reader\XLSX\Manager\OptionsManager as XLSXOptionsManager;
use SpoutX\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use SpoutX\Reader\XLSX\Reader as XLSXReader;

/**
 * Class ReaderFactory
 * This factory is used to create readers, based on the type of the file to be read.
 * Only the XLSX format is supported.
 */
class ReaderFactory
{
    /**
     * Map file extensions to reader types
     * @var array
     */
    private static $extensionReaderMap = [
        'xlsx' => Type::XLSX,
    ];

    /**
     * This creates an instance of the appropriate reader, given the type of the file to be read
     *
     * @param  string $readerType Type of the reader to instantiate
     * @throws \SpoutX\Common\Exception\UnsupportedTypeException
     * @return ReaderInterface
     */
    public static function create($readerType)
    {
        switch ($readerType) {
            case Type::XLSX: return self::getXLSXReader();
            default:
                throw new UnsupportedTypeException('No readers supporting the given type: ' . $readerType);
        }
    }

    /**
     * Creates a reader by file extension
     *
     * @param string The path to the spreadsheet file. Only the .xlsx extension is supported.
     * @throws \SpoutX\Common\Exception\IOException
     * @throws \SpoutX\Common\Exception\UnsupportedTypeException
     * @return ReaderInterface
     */
    public static function createFromFile($path)
    {
        if (!\is_file($path)) {
            throw new IOException(
                sprintf('Could not open "%s" for reading! File does not exist.', $path)
            );
        }

        $ext = \strtolower(\pathinfo($path, PATHINFO_EXTENSION));
        $readerType = self::$extensionReaderMap[$ext] ?? null;
        if ($readerType === null) {
            throw new UnsupportedTypeException(
                sprintf('No readers supporting the file extension "%s".', $ext)
            );
        }

        return self::create($readerType);
    }

    /**
     * @return XLSXReader
     */
    private static function getXLSXReader()
    {
        $optionsManager = new XLSXOptionsManager();
        $helperFactory = new XLSXHelperFactory();
        $managerFactory = new XLSXManagerFactory($helperFactory, new CachingStrategyFactory());
        $entityFactory = new XLSXInternalEntityFactory($managerFactory, $helperFactory);
        $globalFunctionsHelper = $helperFactory->createGlobalFunctionsHelper();

        return new XLSXReader($optionsManager, $globalFunctionsHelper, $entityFactory, $managerFactory);
    }
}
