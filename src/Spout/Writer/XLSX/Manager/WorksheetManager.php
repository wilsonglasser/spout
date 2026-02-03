<?php

namespace WilsonGlasser\Spout\Writer\XLSX\Manager;

use WilsonGlasser\Spout\Common\Entity\Cell;
use WilsonGlasser\Spout\Common\Entity\Row;
use WilsonGlasser\Spout\Common\Entity\Style\Style;
use WilsonGlasser\Spout\Common\Exception\InvalidArgumentException;
use WilsonGlasser\Spout\Common\Exception\IOException;
use WilsonGlasser\Spout\Common\Helper\Escaper\XLSX as XLSXEscaper;
use WilsonGlasser\Spout\Common\Helper\StringHelper;
use WilsonGlasser\Spout\Common\Manager\OptionsManagerInterface;
use WilsonGlasser\Spout\Reader\XLSX\Helper\DateFormatHelper;
use WilsonGlasser\Spout\Writer\Common\Creator\InternalEntityFactory;
use WilsonGlasser\Spout\Writer\Common\Entity\Options;
use WilsonGlasser\Spout\Writer\Common\Entity\Worksheet;
use WilsonGlasser\Spout\Writer\Common\Helper\CellHelper;
use WilsonGlasser\Spout\Writer\Common\Manager\RowManager;
use WilsonGlasser\Spout\Writer\Common\Manager\Style\StyleMerger;
use WilsonGlasser\Spout\Writer\Common\Manager\WorksheetManagerInterface;
use WilsonGlasser\Spout\Writer\XLSX\Manager\Style\StyleManager;

/**
 * Class WorksheetManager
 * XLSX worksheet manager, providing the interfaces to work with XLSX worksheets.
 */
class WorksheetManager implements WorksheetManagerInterface
{
    /**
     * Maximum number of characters a cell can contain
     * @see https://support.office.com/en-us/article/Excel-specifications-and-limits-16c69c74-3d6a-4aaf-ba35-e6eb276e8eaa [Excel 2007]
     * @see https://support.office.com/en-us/article/Excel-specifications-and-limits-1672b34d-7043-467e-8e27-269d656771c3 [Excel 2010]
     * @see https://support.office.com/en-us/article/Excel-specifications-and-limits-ca36e2dc-1f09-4620-b726-67c00b05040f [Excel 2013/2016]
     */
    public const MAX_CHARACTERS_PER_CELL = 32767;

    public const SHEET_XML_FILE_HEADER = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xml:space="preserve" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
           xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing"
           xmlns:x14="http://schemas.microsoft.com/office/spreadsheetml/2009/9/main"
           xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac"
           xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">
EOD;

    /** @var bool Whether inline or shared strings should be used */
    protected $shouldUseInlineStrings;

    /** @var RowManager Manages rows */
    private $rowManager;

    /** @var StyleManager Manages styles */
    private $styleManager;

    /** @var StyleMerger Helper to merge styles together */
    private $styleMerger;

    /** @var SharedStringsManager Helper to write shared strings */
    private $sharedStringsManager;

    /** @var XLSXEscaper Strings escaper */
    private $stringsEscaper;

    /** @var InternalEntityFactory Factory to create entities */
    private $entityFactory;

    private $beforeSheetDataPointer;

    /**
     * @var int[] Max length by column, used for auto size
     */
    private $columnsMaxTextLength = [];

    /**
     * WorksheetManager constructor.
     *
     * @param OptionsManagerInterface $optionsManager
     * @param RowManager $rowManager
     * @param StyleManager $styleManager
     * @param StyleMerger $styleMerger
     * @param SharedStringsManager $sharedStringsManager
     * @param XLSXEscaper $stringsEscaper
     * @param InternalEntityFactory $entityFactory
     */
    public function __construct(
        OptionsManagerInterface $optionsManager,
        RowManager $rowManager,
        StyleManager $styleManager,
        StyleMerger $styleMerger,
        SharedStringsManager $sharedStringsManager,
        XLSXEscaper $stringsEscaper,
        InternalEntityFactory $entityFactory
    ) {
        $this->shouldUseInlineStrings = $optionsManager->getOption(Options::SHOULD_USE_INLINE_STRINGS);
        $this->rowManager = $rowManager;
        $this->styleManager = $styleManager;
        $this->styleMerger = $styleMerger;
        $this->sharedStringsManager = $sharedStringsManager;
        $this->stringsEscaper = $stringsEscaper;
        $this->entityFactory = $entityFactory;
    }

    /**
     * @return SharedStringsManager
     */
    public function getSharedStringsManager()
    {
        return $this->sharedStringsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function startSheet(Worksheet $worksheet)
    {
        $sheetFilePointer = fopen($worksheet->getFilePath(), 'w+');
        $this->throwIfSheetFilePointerIsNotAvailable($sheetFilePointer);

        $worksheet->setFilePointer($sheetFilePointer);

        fwrite($sheetFilePointer, self::SHEET_XML_FILE_HEADER . PHP_EOL);

        $this->beforeSheetDataPointer = ftell($sheetFilePointer);

        fwrite($sheetFilePointer, '<sheetData>' . PHP_EOL);
    }

    /**
     * Checks if the sheet has been sucessfully created. Throws an exception if not.
     *
     * @param bool|resource $sheetFilePointer Pointer to the sheet data file or FALSE if unable to open the file
     * @return void
     * @throws IOException If the sheet data file cannot be opened for writing
     */
    private function throwIfSheetFilePointerIsNotAvailable($sheetFilePointer)
    {
        if (!$sheetFilePointer) {
            throw new IOException('Unable to open sheet for writing.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRow(Worksheet $worksheet, $row)
    {
        if (is_array($row)) {
            if (isset($row[0]) && count($row[0]) > 0) {
                $this->addNonEmptyRow($worksheet, $row);
            }
        } elseif (!$this->rowManager->isEmpty($row)) {
            $this->addNonEmptyRow($worksheet, $row);
        }

        $worksheet->setLastWrittenRowIndex($worksheet->getLastWrittenRowIndex() + 1);
    }

    /**
     * Adds non empty row to the worksheet.
     *
     * @param Worksheet $worksheet The worksheet to add the row to
     * @param Row|array $row The row to be written
     * @return void
     * @throws InvalidArgumentException If a cell value's type is not supported
     * @throws IOException If the data cannot be written
     */
    private function addNonEmptyRow(Worksheet $worksheet, $row)
    {
        $cellIndex = 0;

        if (is_array($row)) {
            $rowStyle = isset($row[1]) ? $row[1] : Style::defaultStyle();
            $numCells = count($row[0]);
            $cells = $row[0];
        } else {
            $rowStyle = $row->getStyle();
            $numCells = $row->getNumCells();
            $cells = $row->getCells();
        }
        $rowIndex = $worksheet->getLastWrittenRowIndex() + 1;

        $rowXML = '<row r="' . $rowIndex . '" spans="1:' . $numCells . '" ';

        if (!empty($rowStyle->getHeight())) {
            $rowXML .= ' ht="' . $rowStyle->getHeight() . '" customHeight="1" ';
        }

        $rowXML .= '  >' . PHP_EOL;

        foreach ($cells as $cell) {
            $rowXML .= "\t" . $this->applyStyleAndGetCellXML($cell, $rowStyle, $rowIndex, $cellIndex);
            $cellIndex++;
        }

        $rowXML .= '</row>' . PHP_EOL;

        $wasWriteSuccessful = \fwrite($worksheet->getFilePointer(), $rowXML);
        if ($wasWriteSuccessful === false) {
            throw new IOException("Unable to write data in {$worksheet->getFilePath()}");
        }
    }

    /**
     * Applies styles to the given style, merging the cell's style with its row's style
     *
     * @param Cell|array $cell
     * @param Style $rowStyle
     * @param int $rowIndex
     * @param int $cellIndex
     * @return string
     * @throws InvalidArgumentException If the given value cannot be processed
     */
    private function applyStyleAndGetCellXML($cell, Style $rowStyle, $rowIndex, $cellIndex)
    {
        $isObject = $cell instanceof Cell;
        // Apply row and extra styles
        if ($isObject) {
            $cellStyle = $cell->getStyle();
        } else {
            $cellStyle = isset($cell[2]) ? $cell[2] : null;
        }
        $mergedCellAndRowStyle = $this->styleMerger->merge($cellStyle, $rowStyle);

        if ($isObject) {
            $cell->setStyle($mergedCellAndRowStyle);
        } else {
            $cell[2] = $mergedCellAndRowStyle;
        }
        $newCellStyle = $this->styleManager->applyExtraStylesIfNeeded($cell);

        if ($newCellStyle->isUpdated()) {
            $registeredStyle = $this->styleManager->registerStyle($newCellStyle->getStyle());
        } else {
            $registeredStyle = $this->styleManager->registerStyle($mergedCellAndRowStyle);
        }

        return $this->getCellXML($rowIndex, $cellIndex, $cell, $registeredStyle->getId());
    }

    /**
     * @return int[]
     *
     */
    public function getColumnsMaxTextLength()
    {
        return $this->columnsMaxTextLength;
    }

    /**
     * Increment max length for column
     * @param string $columnIndex
     * @param string $text
     * @return string
     */
    protected function setColumnMaxCharacters($columnIndex, $text)
    {
        if (strpos($text, "\n") !== false) {
            $lineTexts = explode("\n", $text);
            $lineWidths = array();
            foreach ($lineTexts as $lineText) {
                $lineWidths[] = StringHelper::getStringLength($lineText);
            }
            $length = max($lineWidths); // width of longest line in cell
        } else {
            $length = StringHelper::getStringLength($text);
        }

        if (!isset($this->columnsMaxTextLength[$columnIndex])) {
            $this->columnsMaxTextLength[$columnIndex] = $length;
        } else {
            $this->columnsMaxTextLength[$columnIndex] = max($this->columnsMaxTextLength[$columnIndex], $length);
        }
        return $text;
    }

    /**
     * Builds and returns xml for a single cell.
     *
     * @param int $rowIndex
     * @param int $cellNumber
     * @param Cell|array $cell
     * @param int $styleId
     * @return string
     * @throws InvalidArgumentException If the given value cannot be processed
     */
    private function getCellXML($rowIndex, $cellNumber, $cell, $styleId)
    {
        $columnIndex = CellHelper::getColumnLettersFromColumnIndex($cellNumber);
        $cellXML = '<c r="' . $columnIndex . $rowIndex . '"';
        $cellXML .= ' s="' . $styleId . '"';

        if ($cell instanceof Cell) {
            $type = $cell->getType();
            if ($cell->isFormula()) {
                $value = [$cell->getValue(), $cell->getFormula()];
            } else {
                $value = $cell->getValue();
            }
        } else {
            $type = $cell[0];
            $value = $cell[1];
        }

        if ($value === null) {
            $value = '';
        }

        if ($type === Cell::TYPE_STRING && preg_match('/[^-.0-9]/', $value)) {
            $cellXML .= $this->getCellXMLFragmentForNonEmptyString($this->setColumnMaxCharacters($columnIndex, $value));
        } elseif ($type === Cell::TYPE_FORMULA) {
            $formulaType = '';
            if (is_string($value[0]) && !is_numeric($value[0])) {
                $formulaType = ' t="str"';
            } elseif (is_bool($value[0])) {
                $formulaType = ' t="b"';
            }
            $cellXML .= $formulaType . '><f>' . $value[1] . '</f><v>' . $this->setColumnMaxCharacters($columnIndex, $value[0]) . '</v></c>';
        } elseif ($type === Cell::TYPE_BOOLEAN) {
            $cellXML .= ' t="b"><v>' . $this->setColumnMaxCharacters($columnIndex, (int)($value)) . '</v></c>';
        } elseif ($type === Cell::TYPE_NUMERIC || ($type == Cell::TYPE_STRING && !preg_match('/[^-.0-9]/', $value) && is_numeric($value))) {
            $cellXML .= '><v>' . $this->setColumnMaxCharacters($columnIndex, StringHelper::formatNumericValue($value)) . '</v></c>';
        } elseif ($type === Cell::TYPE_DATE) {
            $cellXML .= '><v>' . $this->setColumnMaxCharacters($columnIndex, DateFormatHelper::toExcelDateFormat($value)) . '</v></c>';
        } elseif ($type === Cell::TYPE_ERROR && $cell instanceof Cell && is_string($cell->getValueEvenIfError())) {
            // only writes the error value if it's a string
            $cellXML .= ' t="e"><v>' . $cell->getValueEvenIfError() . '</v></c>';
        } elseif ($type === Cell::TYPE_EMPTY || empty($value)) {
            if ($this->styleManager->shouldApplyStyleOnEmptyCell($styleId)) {
                $cellXML .= '/>';
            } else {
                // don't write empty cells that do no need styling
                // NOTE: not appending to $cellXML is the right behavior!!
                $cellXML = '';
            }
        } elseif ($type === Cell::TYPE_STRING && !preg_match('/[^-.0-9]/', $value)) {
            $cellXML .= $this->getCellXMLFragmentForNonEmptyString($this->setColumnMaxCharacters($columnIndex, $value));
        } else {
            throw new InvalidArgumentException('Trying to add a value with an unsupported type: ' . gettype($value));
        }

        return $cellXML . PHP_EOL;
    }

    /**
     * Returns the XML fragment for a cell containing a non empty string
     *
     * @param string $cellValue The cell value
     * @return string The XML fragment representing the cell
     * @throws InvalidArgumentException If the string exceeds the maximum number of characters allowed per cell
     */
    private function getCellXMLFragmentForNonEmptyString($cellValue)
    {
        if (StringHelper::getStringLength($cellValue) > self::MAX_CHARACTERS_PER_CELL) {
            throw new InvalidArgumentException('Trying to add a value that exceeds the maximum number of characters allowed in a cell (32,767)');
        }

        if ($this->shouldUseInlineStrings) {
            $cellXMLFragment = ' t="inlineStr"><is><t>' . $this->stringsEscaper->escape($cellValue) . '</t></is></c>';
        } else {
            $sharedStringId = $this->sharedStringsManager->writeString($cellValue);
            $cellXMLFragment = ' t="s"><v>' . $sharedStringId . '</v></c>';
        }

        return $cellXMLFragment;
    }

    /**
     * {@inheritdoc}
     */
    public function close(Worksheet $worksheet, Style $defaultStyle = null)
    {
        $worksheetFilePointer = $worksheet->getFilePointer();

        if (!\is_resource($worksheetFilePointer)) {
            return;
        }

        fwrite($worksheetFilePointer, '</sheetData>' . PHP_EOL);

        $sheet = $worksheet->getExternalSheet();

        if (count($sheet->getColumnDimensions())) {
            // I didn't found a way to append a file in the middle without storing all content =/
            $afterContent = stream_get_contents($worksheetFilePointer, -1, $this->beforeSheetDataPointer);

            fseek($worksheetFilePointer, $this->beforeSheetDataPointer);

            fwrite($worksheetFilePointer, '<cols>' . PHP_EOL);
            /**
             * Autosize columns
             */

            $sheet->calculateColumnWidths($this->getColumnsMaxTextLength(), $defaultStyle);

            foreach ($sheet->getColumnDimensions() as $columnDimension) {
                $cellIndex = CellHelper::getColumnToIndexFromCellIndex($columnDimension->getColumnIndex()) + 1;
                $attributes = [
                    'min' => $cellIndex,
                    'max' => $cellIndex,
                    'width' => $columnDimension->getWidth() + ($sheet->getAutoFilter() !== null ? 2 : 0),
                    'customWidth' => 'true'
                ];

                // Column visibility
                if ($columnDimension->getVisible() == false) {
                    $attributes['hidden'] = 'true';
                }

                // Auto size?
                if ($columnDimension->getAutoSize()) {
                    $attributes['bestFit'] = 'true';
                }

                // Collapsed
                if ($columnDimension->getCollapsed() == true) {
                    $attributes['collapsed'] = 'true';
                }

                // Outline level
                if ($columnDimension->getOutlineLevel() > 0) {
                    $attributes['outlineLevel'] = $columnDimension->getOutlineLevel();
                }

                $xml = '';
                foreach ($attributes as $k => $v) {
                    $xml .= $k . '="' . $v . '" ';
                }

                fwrite($worksheetFilePointer, "\t" . '<col ' . $xml . ' />' . PHP_EOL);
            }

            fwrite($worksheetFilePointer, '</cols>' . PHP_EOL);

            fwrite($worksheetFilePointer, $afterContent);
            unset($afterContent);
        }

        if ($sheet->getAutoFilter() !== null) {
            fwrite($worksheetFilePointer, ' <autoFilter ref="' . $sheet->getAutoFilter() . '"><extLst /></autoFilter>' . PHP_EOL);
        }
        if (count($sheet->getMergeCells()) > 0) {
            fwrite($worksheetFilePointer, '<mergeCells>' . PHP_EOL);
            foreach ($sheet->getMergeCells() as $mergeCell) {
                fwrite($worksheetFilePointer, "\t" . ' <mergeCell ref="' . $mergeCell . '"/>' . PHP_EOL);
            }
            fwrite($worksheetFilePointer, '</mergeCells>' . PHP_EOL);
        }

        if (count($worksheet->getExternalSheet()->getComments())) {
            fwrite($worksheetFilePointer, '<legacyDrawing r:id="rId_comments_vml' . $worksheet->getId() . '"/>');
        }

        fwrite($worksheetFilePointer, '</worksheet>');
        fclose($worksheetFilePointer);
    }
}
