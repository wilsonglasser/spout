<?php

declare(strict_types=1);

namespace SpoutX\Reader\XLSX\Creator;

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\Row;
use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Reader\Common\Creator\InternalEntityFactoryInterface;
use SpoutX\Reader\Common\Entity\Options;
use SpoutX\Reader\Common\XMLProcessor;
use SpoutX\Reader\Wrapper\XMLReader;
use SpoutX\Reader\XLSX\Manager\SharedStringsManager;
use SpoutX\Reader\XLSX\RowIterator;
use SpoutX\Reader\XLSX\Sheet;
use SpoutX\Reader\XLSX\SheetIterator;

/**
 * Class InternalEntityFactory
 * Factory to create entities
 */
class InternalEntityFactory implements InternalEntityFactoryInterface
{
    /** @var HelperFactory */
    private HelperFactory $helperFactory;

    /** @var ManagerFactory */
    private ManagerFactory $managerFactory;

    public function __construct(ManagerFactory $managerFactory, HelperFactory $helperFactory)
    {
        $this->managerFactory = $managerFactory;
        $this->helperFactory = $helperFactory;
    }

    /**
     * @param string $filePath Path of the file to be read
     * @param \SpoutX\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @return SheetIterator
     */
    public function createSheetIterator(string $filePath, OptionsManagerInterface $optionsManager, SharedStringsManager $sharedStringsManager): SheetIterator
    {
        $sheetManager = $this->managerFactory->createSheetManager(
            $filePath,
            $optionsManager,
            $sharedStringsManager,
            $this
        );

        return new SheetIterator($sheetManager);
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $sheetDataXMLFilePath Path of the sheet data XML file as in [Content_Types].xml
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param bool $isSheetVisible Whether the sheet is visible
     * @param \SpoutX\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @return Sheet
     */
    public function createSheet(
        string $filePath,
        string $sheetDataXMLFilePath,
        int $sheetIndex,
        string $sheetName,
        bool $isSheetActive,
        bool $isSheetVisible,
        OptionsManagerInterface $optionsManager,
        SharedStringsManager $sharedStringsManager
    ): Sheet {
        $rowIterator = $this->createRowIterator($filePath, $sheetDataXMLFilePath, $optionsManager, $sharedStringsManager);

        return new Sheet($rowIterator, $sheetIndex, $sheetName, $isSheetActive, $isSheetVisible, $filePath, $sheetDataXMLFilePath);
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $sheetDataXMLFilePath Path of the sheet data XML file as in [Content_Types].xml
     * @param \SpoutX\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @return RowIterator
     */
    private function createRowIterator(string $filePath, string $sheetDataXMLFilePath, OptionsManagerInterface $optionsManager, SharedStringsManager $sharedStringsManager): RowIterator
    {
        $xmlReader = $this->createXMLReader();
        $xmlProcessor = $this->createXMLProcessor($xmlReader);

        $styleManager = $this->managerFactory->createStyleManager($filePath, $this);
        $rowManager = $this->managerFactory->createRowManager($this);
        $shouldFormatDates = $optionsManager->getOption(Options::SHOULD_FORMAT_DATES);
        $shouldUse1904Dates = $optionsManager->getOption(Options::SHOULD_USE_1904_DATES);

        $cellValueFormatter = $this->helperFactory->createCellValueFormatter(
            $sharedStringsManager,
            $styleManager,
            $shouldFormatDates,
            $shouldUse1904Dates
        );

        $shouldPreserveEmptyRows = $optionsManager->getOption(Options::SHOULD_PRESERVE_EMPTY_ROWS);

        return new RowIterator(
            $filePath,
            $sheetDataXMLFilePath,
            $shouldPreserveEmptyRows,
            $xmlReader,
            $xmlProcessor,
            $cellValueFormatter,
            $rowManager,
            $this
        );
    }

    /**
     * @param Cell[] $cells
     * @return Row
     */
    public function createRow(array $cells = []): Row
    {
        return new Row($cells, null);
    }

    /**
     * @param mixed $cellValue
     * @return Cell
     */
    public function createCell(mixed $cellValue): Cell
    {
        return new Cell($cellValue);
    }

    /**
     * @return \ZipArchive
     */
    public function createZipArchive(): \ZipArchive
    {
        return new \ZipArchive();
    }

    /**
     * @return XMLReader
     */
    public function createXMLReader(): XMLReader
    {
        return new XMLReader();
    }

    /**
     * @return XMLProcessor
     */
    public function createXMLProcessor(XMLReader $xmlReader): XMLProcessor
    {
        return new XMLProcessor($xmlReader);
    }
}
