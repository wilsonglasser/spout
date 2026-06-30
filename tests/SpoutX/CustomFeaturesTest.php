<?php

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\ColumnDimension;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * Locks the fork's extra XLSX features (the reason this fork exists) before any
 * refactor: merge cells, auto filter, column dimensions and formula cells with a
 * pre-computed value. Assertions run against the raw XML inside the produced .xlsx.
 */
final class CustomFeaturesTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-features';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/features.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testWriterEmitsExtraFeaturesInSheetXml(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);

        $sheet = $writer->getCurrentSheet();
        $sheet->mergeCells('A1:B1');
        $sheet->setAutoFilter('A1:C1');
        $sheet->addColumnDimension(new ColumnDimension('A', 42));

        $writer->addRow(WriterEntityFactory::createRowFromArray(['Title', 'b', 'c']));

        $formulaCell = new Cell('=A1');
        $formulaCell->setCalculatedValue('123');
        $writer->addRow(WriterEntityFactory::createRow([$formulaCell]));

        $writer->close();

        $sheetXml = $this->readFromXlsx('xl/worksheets/sheet1.xml');

        self::assertStringContainsString('<mergeCell ref="A1:B1"', $sheetXml, 'merge cells missing');
        self::assertStringContainsString('autoFilter', $sheetXml, 'auto filter missing');
        self::assertStringContainsString('<col ', $sheetXml, 'column dimension missing');
        self::assertStringContainsString('A1', $sheetXml, 'formula reference missing');
        self::assertStringContainsString('123', $sheetXml, 'formula calculated value missing');
    }

    private function readFromXlsx(string $innerPath): string
    {
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true, 'cannot open produced xlsx');
        $contents = $zip->getFromName($innerPath);
        $zip->close();
        self::assertIsString($contents, "missing $innerPath in xlsx");

        return $contents;
    }
}
