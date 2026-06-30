<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Creator;

use SpoutX\Common\Exception\UnsupportedTypeException;
use SpoutX\Common\Helper\GlobalFunctionsHelper;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\Style\StyleBuilder;
use SpoutX\Writer\WriterInterface;
use SpoutX\Writer\XLSX\Creator\HelperFactory as XLSXHelperFactory;
use SpoutX\Writer\XLSX\Creator\ManagerFactory as XLSXManagerFactory;
use SpoutX\Writer\XLSX\Manager\OptionsManager as XLSXOptionsManager;
use SpoutX\Writer\XLSX\Writer as XLSXWriter;

/**
 * Class WriterFactory
 * This factory is used to create writers, based on the type of the file to be read.
 * Only the XLSX format is supported.
 */
class WriterFactory
{
    /**
     * This creates an instance of the appropriate writer, given the type of the file to be read
     *
     * @param  string $writerType Type of the writer to instantiate
     * @throws \SpoutX\Common\Exception\UnsupportedTypeException
     * @return WriterInterface
     */
    public function create($writerType)
    {
        switch ($writerType) {
            case Type::XLSX: return $this->getXLSXWriter();
            default:
                throw new UnsupportedTypeException('No writers supporting the given type: ' . $writerType);
        }
    }

    /**
     * @return XLSXWriter
     */
    private function getXLSXWriter()
    {
        $styleBuilder = new StyleBuilder();
        $optionsManager = new XLSXOptionsManager($styleBuilder);
        $globalFunctionsHelper = new GlobalFunctionsHelper();

        $helperFactory = new XLSXHelperFactory();
        $managerFactory = new XLSXManagerFactory(new InternalEntityFactory(), $helperFactory);

        return new XLSXWriter($optionsManager, $globalFunctionsHelper, $helperFactory, $managerFactory);
    }
}
