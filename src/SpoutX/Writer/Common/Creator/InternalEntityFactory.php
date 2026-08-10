<?php

declare(strict_types=1);

namespace SpoutX\Writer\Common\Creator;

use SpoutX\Writer\Common\Entity\Sheet;
use SpoutX\Writer\Common\Entity\Workbook;
use SpoutX\Writer\Common\Entity\Worksheet;
use SpoutX\Writer\Common\Manager\SheetManager;

/**
 * Class InternalEntityFactory
 * Factory to create internal entities
 */
class InternalEntityFactory
{
    public function createWorkbook(): Workbook
    {
        return new Workbook();
    }

    public function createWorksheet(string $worksheetFilePath, Sheet $externalSheet): Worksheet
    {
        return new Worksheet($worksheetFilePath, $externalSheet);
    }

    /**
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $associatedWorkbookId ID of the sheet's associated workbook
     * @param SheetManager $sheetManager To manage sheets
     */
    public function createSheet(int $sheetIndex, string $associatedWorkbookId, SheetManager $sheetManager): Sheet
    {
        return new Sheet($sheetIndex, $associatedWorkbookId, $sheetManager);
    }

    public function createZipArchive(): \ZipArchive
    {
        return new \ZipArchive();
    }
}
