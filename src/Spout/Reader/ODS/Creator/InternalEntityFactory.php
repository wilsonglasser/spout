<?php

namespace WilsonGlasser\Spout\Reader\ODS\Creator;

use WilsonGlasser\Spout\Common\Entity\Cell;
use WilsonGlasser\Spout\Common\Entity\Row;
use WilsonGlasser\Spout\Reader\Common\Creator\InternalEntityFactoryInterface;
use WilsonGlasser\Spout\Reader\Common\Entity\Options;
use WilsonGlasser\Spout\Reader\Common\XMLProcessor;
use WilsonGlasser\Spout\Reader\ODS\RowIterator;
use WilsonGlasser\Spout\Reader\ODS\Sheet;
use WilsonGlasser\Spout\Reader\ODS\SheetIterator;
use WilsonGlasser\Spout\Reader\Wrapper\XMLReader;

/**
 * Class EntityFactory
 * Factory to create entities
 */
class InternalEntityFactory implements InternalEntityFactoryInterface
{
    /** @var HelperFactory */
    private $helperFactory;

    /** @var ManagerFactory */
    private $managerFactory;

    /**
     * @param HelperFactory $helperFactory
     * @param ManagerFactory $managerFactory
     */
    public function __construct(HelperFactory $helperFactory, ManagerFactory $managerFactory)
    {
        $this->helperFactory = $helperFactory;
        $this->managerFactory = $managerFactory;
    }

    /**
     * @param string $filePath Path of the file to be read
     * @param \WilsonGlasser\Spout\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @return SheetIterator
     */
    public function createSheetIterator($filePath, $optionsManager)
    {
        $escaper = $this->helperFactory->createStringsEscaper();
        $settingsHelper = $this->helperFactory->createSettingsHelper($this);

        return new SheetIterator($filePath, $optionsManager, $escaper, $settingsHelper, $this);
    }

    /**
     * @param XMLReader $xmlReader XML Reader
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param bool $isSheetVisible Whether the sheet is visible
     * @param \WilsonGlasser\Spout\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @return Sheet
     */
    public function createSheet($xmlReader, $sheetIndex, $sheetName, $isSheetActive, $isSheetVisible, $optionsManager)
    {
        $rowIterator = $this->createRowIterator($xmlReader, $optionsManager);

        return new Sheet($rowIterator, $sheetIndex, $sheetName, $isSheetActive, $isSheetVisible);
    }

    /**
     * @param XMLReader $xmlReader XML Reader
     * @param \WilsonGlasser\Spout\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @return RowIterator
     */
    private function createRowIterator($xmlReader, $optionsManager)
    {
        $shouldFormatDates = $optionsManager->getOption(Options::SHOULD_FORMAT_DATES);
        $cellValueFormatter = $this->helperFactory->createCellValueFormatter($shouldFormatDates);
        $xmlProcessor = $this->createXMLProcessor($xmlReader);
        $rowManager = $this->managerFactory->createRowManager($this);

        return new RowIterator($xmlReader, $optionsManager, $cellValueFormatter, $xmlProcessor, $rowManager, $this);
    }

    /**
     * @param Cell[] $cells
     * @return Row
     */
    public function createRow(array $cells = [])
    {
        return new Row($cells, null);
    }

    /**
     * @param mixed $cellValue
     * @return Cell
     */
    public function createCell($cellValue)
    {
        return new Cell($cellValue);
    }

    /**
     * @return XMLReader
     */
    public function createXMLReader()
    {
        return new XMLReader();
    }

    /**
     * @param XMLReader $xmlReader
     * @return XMLProcessor
     */
    private function createXMLProcessor($xmlReader)
    {
        return new XMLProcessor($xmlReader);
    }

    /**
     * @return \ZipArchive
     */
    public function createZipArchive()
    {
        return new \ZipArchive();
    }
}
