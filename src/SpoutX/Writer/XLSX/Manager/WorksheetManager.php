<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Manager;

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\CellType;
use SpoutX\Common\Entity\Row;
use SpoutX\Common\Entity\Style\Style;
use SpoutX\Common\Exception\InvalidArgumentException;
use SpoutX\Common\Exception\IOException;
use SpoutX\Common\Helper\Escaper\XLSX as XLSXEscaper;
use SpoutX\Common\Helper\StringHelper;
use SpoutX\Common\Manager\OptionsManagerInterface;
use SpoutX\Reader\XLSX\Helper\DateFormatHelper;
use SpoutX\Writer\Common\Creator\InternalEntityFactory;
use SpoutX\Writer\Common\Entity\Options;
use SpoutX\Writer\Common\Entity\Worksheet;
use SpoutX\Writer\Common\Helper\CellHelper;
use SpoutX\Writer\Common\Manager\RowManager;
use SpoutX\Writer\Common\Manager\Style\StyleMerger;
use SpoutX\Writer\Common\Manager\WorksheetManagerInterface;
use SpoutX\Writer\XLSX\Manager\Style\StyleManager;

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
    protected bool $shouldUseInlineStrings;

    /** @var RowManager Manages rows */
    private RowManager $rowManager;

    /** @var StyleManager Manages styles */
    private StyleManager $styleManager;

    /** @var StyleMerger Helper to merge styles together */
    private StyleMerger $styleMerger;

    /** @var SharedStringsManager Helper to write shared strings */
    private SharedStringsManager $sharedStringsManager;

    /** @var XLSXEscaper Strings escaper */
    private XLSXEscaper $stringsEscaper;

    /** @var InternalEntityFactory Factory to create entities */
    private InternalEntityFactory $entityFactory;

    private $beforeSheetDataPointer;

    /**
     * @var int[] Max length by column, used for auto size
     */
    private array $columnsMaxTextLength = [];

    /**
     * WorksheetManager constructor.
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

    public function getSharedStringsManager(): SharedStringsManager
    {
        return $this->sharedStringsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function startSheet(Worksheet $worksheet): void
    {
        $sheetFilePointer = fopen($worksheet->getFilePath(), 'w+');
        $this->throwIfSheetFilePointerIsNotAvailable($sheetFilePointer);

        $worksheet->setFilePointer($sheetFilePointer);

        fwrite($sheetFilePointer, self::SHEET_XML_FILE_HEADER.PHP_EOL);

        $this->beforeSheetDataPointer = ftell($sheetFilePointer);

        fwrite($sheetFilePointer, '<sheetData>'.PHP_EOL);
    }

    /**
     * Checks if the sheet has been sucessfully created. Throws an exception if not.
     *
     * @param bool|resource $sheetFilePointer Pointer to the sheet data file or FALSE if unable to open the file
     * @throws IOException If the sheet data file cannot be opened for writing
     */
    private function throwIfSheetFilePointerIsNotAvailable($sheetFilePointer): void
    {
        if (!$sheetFilePointer) {
            throw new IOException('Unable to open sheet for writing.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRow(Worksheet $worksheet, Row|array $row): void
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
     * @throws InvalidArgumentException If a cell value's type is not supported
     * @throws IOException If the data cannot be written
     */
    private function addNonEmptyRow(Worksheet $worksheet, Row|array $row): void
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
            $rowXML .= ' ht="'.$rowStyle->getHeight() .'" customHeight="1" ';
        }

        $rowXML .= '  >'.PHP_EOL;

        foreach ($cells as $cell) {
            $rowXML .= "\t".$this->applyStyleAndGetCellXML($cell, $rowStyle, $rowIndex, $cellIndex);
            $cellIndex++;
        }

        $rowXML .= '</row>'.PHP_EOL;

        $wasWriteSuccessful = fwrite($worksheet->getFilePointer(), $rowXML);
        if ($wasWriteSuccessful === false) {
            throw new IOException("Unable to write data in {$worksheet->getFilePath()}");
        }
    }

    /**
     * Applies styles to the given style, merging the cell's style with its row's style
     * Then builds and returns xml for the cell.
     *
     * @throws InvalidArgumentException If the given value cannot be processed
     */
    private function applyStyleAndGetCellXML(Cell|array $cell, Style $rowStyle, int $rowIndex, int $cellIndex): string
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

        $registeredStyle = $this->styleManager->registerStyle($newCellStyle);

        return $this->getCellXML($rowIndex, $cellIndex, $cell, $registeredStyle->getId());
    }

    /**
     * @return int[]
     *
     */
    public function getColumnsMaxTextLength(): array
    {
        return $this->columnsMaxTextLength;
    }

    /**
     * Increment max length for column
     */
    protected function setColumnMaxCharacters(string $columnIndex, int|float|string|bool $text): string
    {
        $text = (string) $text;

        if (strpos($text, "\n") !== false) {
            $lineTexts = explode("\n", $text);
            $lineWidths = [];
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
     * @throws InvalidArgumentException If the given value cannot be processed
     */
    private function getCellXML(int $rowIndex, int $cellNumber, Cell|array $cell, int $styleId): string
    {
        $columnIndex = CellHelper::getCellIndexFromColumnIndex($cellNumber);
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

        if ($type === CellType::String && preg_match('/[^-.0-9]/', $value)) {
            $cellXML .= $this->getCellXMLFragmentForNonEmptyString($this->setColumnMaxCharacters($columnIndex, $value));
        } elseif ($type === CellType::Formula) {
            $formulaType = '';
            if (is_string($value[0]) && !is_numeric($value[0])) {
                $formulaType = ' t="str"';
            } elseif (is_bool($value[0])) {
                $formulaType = ' t="b"';
            }
            $cellXML .= $formulaType . '><f>' . $value[1]. '</f><v>' . $this->setColumnMaxCharacters($columnIndex, $value[0]) . '</v></c>';
        } elseif ($type === CellType::Boolean) {
            $cellXML .= ' t="b"><v>' . $this->setColumnMaxCharacters($columnIndex, (int)($value)) . '</v></c>';
        } elseif ($type === CellType::Numeric || ($type == CellType::String && !preg_match('/[^-.0-9]/', $value) && is_numeric($value))) {
            $cellXML .= ' t="n"><v>' . $this->setColumnMaxCharacters($columnIndex, $value) . '</v></c>';
        } elseif ($type === CellType::Date) {
            $cellXML .= ' t="n"><v>' . $this->setColumnMaxCharacters($columnIndex, DateFormatHelper::toExcelDateFormat($value)) . '</v></c>';
        } elseif ($type === CellType::Empty || empty($value)) {
            if ($this->styleManager->shouldApplyStyleOnEmptyCell($styleId)) {
                $cellXML .= '/>';
            } else {
                // don't write empty cells that do no need styling
                // NOTE: not appending to $cellXML is the right behavior!!
                $cellXML = '';
            }
        } elseif ($type === CellType::String && !preg_match('/[^-.0-9]/', $value)) {
            $cellXML .= $this->getCellXMLFragmentForNonEmptyString($this->setColumnMaxCharacters($columnIndex, $value));
        } else {
            throw new InvalidArgumentException('Trying to add a value with an unsupported type: ' . gettype($value));
        }


        return $cellXML.PHP_EOL;
    }

    /**
     * Returns the XML fragment for a cell containing a non empty string
     *
     * @param int|float|string|bool $cellValue The cell value
     * @return string The XML fragment representing the cell
     * @throws InvalidArgumentException If the string exceeds the maximum number of characters allowed per cell
     */
    private function getCellXMLFragmentForNonEmptyString(int|float|string|bool $cellValue): string
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
    public function close(Worksheet $worksheet, ?Style $defaultStyle = null): void
    {
        $worksheetFilePointer = $worksheet->getFilePointer();

        if (!is_resource($worksheetFilePointer)) {
            return;
        }

        fwrite($worksheetFilePointer, '</sheetData>'.PHP_EOL);


        $sheet = $worksheet->getExternalSheet();

        $pageSetup = $sheet->getPageSetup();
        $sheetView = $sheet->getSheetView();
        $hasColumnDimensions = count($sheet->getColumnDimensions()) > 0;
        $needsFitToPagePr = $pageSetup !== null && $pageSetup->isFitToPage();

        // Content that must appear BEFORE <sheetData> (in CT_Worksheet order:
        // <sheetPr> then <sheetViews> then <cols>) has to be spliced in ahead of the
        // already-streamed sheet data. We buffer everything after the insertion point
        // and rewrite it.
        if ($hasColumnDimensions || $needsFitToPagePr || $sheetView !== null) {

            // I didn't found a way to append a file in the middle without storing all content =/
            $afterContent =  stream_get_contents($worksheetFilePointer, -1, $this->beforeSheetDataPointer);

            fseek($worksheetFilePointer, $this->beforeSheetDataPointer);

            // <sheetPr> precedes <sheetViews> and <cols> in the schema
            if ($needsFitToPagePr) {
                fwrite($worksheetFilePointer, '<sheetPr><pageSetUpPr fitToPage="true"/></sheetPr>'.PHP_EOL);
            }

            // <sheetViews> precedes <cols>
            if ($sheetView !== null) {
                fwrite($worksheetFilePointer, '<sheetViews>' . $sheetView->getXml() . '</sheetViews>'.PHP_EOL);
            }

            if ($hasColumnDimensions) {
                fwrite($worksheetFilePointer, '<cols>'.PHP_EOL);
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
                        'customWidth' => 'true',
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
                        $xml .= $k.'="'.$v.'" ';
                    }

                    fwrite($worksheetFilePointer, "\t".'<col '.$xml.' />'.PHP_EOL);
                }

                fwrite($worksheetFilePointer, '</cols>'.PHP_EOL);
            }

            fwrite($worksheetFilePointer, $afterContent);
            unset($afterContent);
        }

        if ($sheet->getAutoFilter() !== null) {
            fwrite($worksheetFilePointer, ' <autoFilter ref="' . $sheet->getAutoFilter() . '"><extLst /></autoFilter>'.PHP_EOL);
        }
        if (count($sheet->getMergeCells()) > 0) {
            fwrite($worksheetFilePointer, '<mergeCells>'.PHP_EOL);
            foreach ($sheet->getMergeCells() as $mergeCell) {
                fwrite($worksheetFilePointer, "\t".' <mergeCell ref="' . $mergeCell . '"/>'.PHP_EOL);
            }
            fwrite($worksheetFilePointer, '</mergeCells>'.PHP_EOL);
        }

        // <hyperlinks> precedes the print settings in the CT_Worksheet sequence.
        // Relationship IDs (rId_hyperlink{N}) are assigned by 1-based insertion order
        // and must match the worksheet .rels file (see FileSystemHelper).
        $hyperlinks = $sheet->getHyperlinks();
        if (count($hyperlinks) > 0) {
            fwrite($worksheetFilePointer, '<hyperlinks>'.PHP_EOL);
            $hyperlinkId = 1;
            foreach ($hyperlinks as $cellRef => $url) {
                fwrite($worksheetFilePointer, "\t".'<hyperlink ref="' . $cellRef . '" r:id="rId_hyperlink' . $hyperlinkId . '"/>'.PHP_EOL);
                $hyperlinkId++;
            }
            fwrite($worksheetFilePointer, '</hyperlinks>'.PHP_EOL);
        }

        // Print settings (pageMargins -> pageSetup -> headerFooter) must precede
        // the legacy drawing (comments) in the CT_Worksheet element sequence.
        $pageMargin = $sheet->getPageMargin();
        if ($pageMargin !== null) {
            fwrite($worksheetFilePointer, '<pageMargins top="' . $pageMargin->top . '" right="' . $pageMargin->right
                . '" bottom="' . $pageMargin->bottom . '" left="' . $pageMargin->left
                . '" header="' . $pageMargin->header . '" footer="' . $pageMargin->footer . '"/>'.PHP_EOL);
        }

        if ($pageSetup !== null) {
            $pageSetupXml = '<pageSetup';
            if ($pageSetup->pageOrientation !== null) {
                $pageSetupXml .= ' orientation="' . $pageSetup->pageOrientation->value . '"';
            }
            if ($pageSetup->paperSize !== null) {
                $pageSetupXml .= ' paperSize="' . $pageSetup->paperSize->value . '"';
            }
            if ($pageSetup->fitToHeight !== null) {
                $pageSetupXml .= ' fitToHeight="' . $pageSetup->fitToHeight . '"';
            }
            if ($pageSetup->fitToWidth !== null) {
                $pageSetupXml .= ' fitToWidth="' . $pageSetup->fitToWidth . '"';
            }
            $pageSetupXml .= '/>'.PHP_EOL;
            fwrite($worksheetFilePointer, $pageSetupXml);
        }

        $headerFooter = $sheet->getHeaderFooter();
        if ($headerFooter !== null) {
            $hfXml = '<headerFooter';
            if ($headerFooter->differentOddEven) {
                $hfXml .= ' differentOddEven="1"';
            }
            $hfXml .= '>';
            if ($headerFooter->oddHeader !== null) {
                $hfXml .= '<oddHeader>' . $this->stringsEscaper->escape($headerFooter->oddHeader) . '</oddHeader>';
            }
            if ($headerFooter->oddFooter !== null) {
                $hfXml .= '<oddFooter>' . $this->stringsEscaper->escape($headerFooter->oddFooter) . '</oddFooter>';
            }
            if ($headerFooter->differentOddEven) {
                if ($headerFooter->evenHeader !== null) {
                    $hfXml .= '<evenHeader>' . $this->stringsEscaper->escape($headerFooter->evenHeader) . '</evenHeader>';
                }
                if ($headerFooter->evenFooter !== null) {
                    $hfXml .= '<evenFooter>' . $this->stringsEscaper->escape($headerFooter->evenFooter) . '</evenFooter>';
                }
            }
            $hfXml .= '</headerFooter>'.PHP_EOL;
            fwrite($worksheetFilePointer, $hfXml);
        }

        if (count($worksheet->getExternalSheet()->getComments())) {
            fwrite($worksheetFilePointer, '<legacyDrawing r:id="rId_comments_vml'.$worksheet->getId().'"/>');
        }

        fwrite($worksheetFilePointer, '</worksheet>');
        fclose($worksheetFilePointer);

    }
}
