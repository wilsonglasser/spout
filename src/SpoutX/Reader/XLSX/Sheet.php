<?php

declare(strict_types=1);

namespace SpoutX\Reader\XLSX;

use SpoutX\Reader\SheetInterface;
use SpoutX\Reader\Wrapper\XMLReader;

/**
 * Class Sheet
 * Represents a sheet within a XLSX file
 */
class Sheet implements SheetInterface
{
    /** @var \SpoutX\Reader\XLSX\RowIterator To iterate over sheet's rows */
    protected RowIterator $rowIterator;

    /** @var int Index of the sheet, based on order in the workbook (zero-based) */
    protected int $index;

    /** @var string Name of the sheet */
    protected string $name;

    /** @var bool Whether the sheet was the active one */
    protected bool $isActive;

    /** @var bool Whether the sheet is visible */
    protected bool $isVisible;

    /** @var string Path of the XLSX file being read */
    protected string $filePath;

    /** @var string Path of this sheet's data XML file inside the zip */
    protected string $sheetDataXMLFilePath;

    /** @var string[]|null Lazily-read merge-cell ranges */
    protected ?array $mergeCells = null;

    /**
     * @param RowIterator $rowIterator The corresponding row iterator
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param bool $isSheetVisible Whether the sheet is visible
     * @param string $filePath Path of the XLSX file being read
     * @param string $sheetDataXMLFilePath Path of this sheet's data XML inside the zip
     */
    public function __construct(RowIterator $rowIterator, int $sheetIndex, string $sheetName, bool $isSheetActive, bool $isSheetVisible, string $filePath = '', string $sheetDataXMLFilePath = '')
    {
        $this->rowIterator = $rowIterator;
        $this->index = $sheetIndex;
        $this->name = $sheetName;
        $this->isActive = $isSheetActive;
        $this->isVisible = $isSheetVisible;
        $this->filePath = $filePath;
        $this->sheetDataXMLFilePath = $sheetDataXMLFilePath;
    }

    /**
     * Returns the sheet's merge-cell ranges (e.g. ["A1:B1"]). Read lazily on first
     * call — merge cells live after <sheetData> in the sheet XML.
     *
     * @return string[]
     */
    public function getMergeCells(): array
    {
        if ($this->mergeCells === null) {
            $this->mergeCells = $this->readMergeCells();
        }

        return $this->mergeCells;
    }

    /**
     * @return string[]
     */
    private function readMergeCells(): array
    {
        $mergeCells = [];

        if ($this->filePath === '' || $this->sheetDataXMLFilePath === '') {
            return $mergeCells;
        }

        $xmlReader = new XMLReader();
        if ($xmlReader->openFileInZip($this->filePath, ltrim($this->sheetDataXMLFilePath, '/'))) {
            while ($xmlReader->read()) {
                if ($xmlReader->isPositionedOnStartingNode('mergeCell')) {
                    $ref = $xmlReader->getAttribute('ref');
                    if ($ref !== null) {
                        $mergeCells[] = $ref;
                    }
                }
            }
            $xmlReader->close();
        }

        return $mergeCells;
    }

    /**
     * @return \SpoutX\Reader\XLSX\RowIterator
     */
    public function getRowIterator(): RowIterator
    {
        return $this->rowIterator;
    }

    /**
     * @return int Index of the sheet, based on order in the workbook (zero-based)
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return string Name of the sheet
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool Whether the sheet was defined as active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool Whether the sheet is visible
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }
}
