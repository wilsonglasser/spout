<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Manager;

use SpoutX\Writer\Common\Entity\Sheet;
use SpoutX\Writer\Common\Manager\WorkbookManagerAbstract;
use SpoutX\Writer\XLSX\Helper\FileSystemHelper;
use SpoutX\Writer\XLSX\Manager\Comment\CommentManager;
use SpoutX\Writer\XLSX\Manager\Style\StyleManager;

/**
 * Class WorkbookManager
 * XLSX workbook manager, providing the interfaces to work with workbook.
 */
class WorkbookManager extends WorkbookManagerAbstract
{
    /**
     * Maximum number of rows a XLSX sheet can contain
     * @see http://office.microsoft.com/en-us/excel-help/excel-specifications-and-limits-HP010073849.aspx
     */
    protected static int $maxRowsPerWorksheet = 1048576;

    /** @var WorksheetManager Object used to manage worksheets */
    protected $worksheetManager;

    /** @var StyleManager Manages styles */
    protected $styleManager;

    /** @var FileSystemHelper Helper to perform file system operations */
    protected $fileSystemHelper;

    /**
     * @return int Maximum number of rows/columns a sheet can contain
     */
    protected function getMaxRowsPerWorksheet(): int
    {
        return self::$maxRowsPerWorksheet;
    }

    /**
     * @return string The file path where the data for the given sheet will be stored
     */
    public function getWorksheetFilePath(Sheet $sheet): string
    {
        $worksheetFilesFolder = $this->fileSystemHelper->getXlWorksheetsFolder();

        return $worksheetFilesFolder . '/' . strtolower($sheet->getName()) . '.xml';
    }
    public function getCommentManager(): CommentManager
    {
        return $this->commentManager;
    }

    /**
     * Closes custom objects that are still opened
     */
    protected function closeRemainingObjects(): void
    {
        $this->worksheetManager->getSharedStringsManager()->close();
    }

    /**
     * Writes all the necessary files to disk and zip them together to create the final file.
     *
     * @param resource $finalFilePointer Pointer to the spreadsheet that will be created
     */
    protected function writeAllFilesToDiskAndZipThem($finalFilePointer): void
    {
        $worksheets = $this->getWorksheets();

        $this->fileSystemHelper
            ->createContentTypesFile($worksheets)
            ->createWorkbookFile($worksheets, $this->getWorkbook()->getProtection())
            ->createCommentsFile($this->getCommentManager(), $worksheets)
            ->createWorkbookRelsFile($this->getCommentManager(), $worksheets)
            ->createStylesFile($this->styleManager)
            ->zipRootFolderAndCopyToStream($finalFilePointer);
    }
}
