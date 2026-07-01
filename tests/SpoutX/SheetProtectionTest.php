<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\XLSX\Entity\SheetProtection;
use SpoutX\Writer\XLSX\Entity\SheetVisibility;

/**
 * Locks the XLSX sheet-protection feature and the tab-visibility states
 * (visible / hidden / veryHidden). No golden oracle exists, so the sheet XML is
 * also asserted well-formed and in CT_Worksheet order (sheetProtection before
 * autoFilter).
 */
final class SheetProtectionTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-protection';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/protection.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testSheetProtectionWithPassword(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $sheet = $writer->getCurrentSheet();
        $sheet->setAutoFilter('A1:B1');
        $sheet->setSheetProtection(new SheetProtection(password: 'test', lockSheet: true, lockSort: true));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a', 'b']));
        $writer->close();

        $xml = $this->read('xl/worksheets/sheet1.xml');

        self::assertTrue((new \DOMDocument())->loadXML($xml), 'sheet XML not well-formed');
        self::assertStringContainsString('<sheetProtection ', $xml);
        self::assertStringContainsString('sheet="true"', $xml);
        self::assertStringContainsString('sort="true"', $xml);
        // legacy 16-bit Excel hash of "test"
        self::assertStringContainsString('password="CBEB"', $xml);

        // sheetProtection must come after </sheetData> and before <autoFilter>
        $posData = strpos($xml, '</sheetData>');
        $posProt = strpos($xml, '<sheetProtection');
        $posFilter = strpos($xml, '<autoFilter');
        self::assertTrue($posData < $posProt && $posProt < $posFilter, 'sheetProtection out of order');
    }

    public function testNoProtectionEmitsNothing(): void
    {
        $xml = $this->writeSimple(static function ($sheet): void {
        });
        self::assertStringNotContainsString('<sheetProtection', $xml);
    }

    public function testHiddenAndVeryHiddenStates(): void
    {
        // very hidden
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->getCurrentSheet()->setVisibility(SheetVisibility::VeryHidden);
        // a workbook must keep at least one visible sheet, so add a second visible one
        $writer->addRow(WriterEntityFactory::createRowFromArray(['x']));
        $second = $writer->addNewSheetAndMakeItCurrent();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['y']));
        $writer->close();

        $workbook = $this->read('xl/workbook.xml');
        self::assertTrue((new \DOMDocument())->loadXML($workbook));
        self::assertStringContainsString('state="veryHidden"', $workbook);
        self::assertStringContainsString('state="visible"', $workbook);
    }

    public function testSetIsVisibleFalseMapsToHidden(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->getCurrentSheet()->setIsVisible(false);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['x']));
        $writer->addNewSheetAndMakeItCurrent();
        $writer->addRow(WriterEntityFactory::createRowFromArray(['y']));
        $writer->close();

        $workbook = $this->read('xl/workbook.xml');
        self::assertStringContainsString('state="hidden"', $workbook);
    }

    private function writeSimple(callable $configure): string
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $configure($writer->getCurrentSheet());
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        return $this->read('xl/worksheets/sheet1.xml');
    }

    private function read(string $innerPath): string
    {
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true, 'cannot open produced xlsx');
        $contents = $zip->getFromName($innerPath);
        $zip->close();
        self::assertIsString($contents, "missing $innerPath");

        return $contents;
    }
}
