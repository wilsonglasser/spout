<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * Locks the macro-enabled workbook (.xlsm) output: xl/vbaProject.bin, its relationship and
 * content type, the macroEnabled main part and the code names binding the VBA modules.
 */
final class VbaProjectTest extends TestCase
{
    /** OLE compound file signature followed by filler: enough for the writer, which only embeds the bytes. */
    private const FAKE_VBA_PROJECT = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" . 'fake-vba-project-body';

    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-vba';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/macro.xlsm';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testVbaProjectMakesAMacroEnabledWorkbook(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->setVbaProject(self::FAKE_VBA_PROJECT);
        $writer->getCurrentSheet()->setName('DB');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->addNewSheetAndMakeItCurrent()->setName('Form')->setCodeName('shForm');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['b']));
        $writer->close();

        self::assertSame(self::FAKE_VBA_PROJECT, $this->read('xl/vbaProject.bin'));

        $contentTypes = $this->read('[Content_Types].xml');
        self::assertTrue((new \DOMDocument())->loadXML($contentTypes), '[Content_Types].xml not well-formed');
        self::assertStringContainsString('ContentType="application/vnd.ms-excel.sheet.macroEnabled.main+xml" PartName="/xl/workbook.xml"', $contentTypes);
        self::assertStringContainsString('ContentType="application/vnd.ms-office.vbaProject" PartName="/xl/vbaProject.bin"', $contentTypes);
        self::assertStringNotContainsString('spreadsheetml.sheet.main+xml', $contentTypes);

        $rels = $this->read('xl/_rels/workbook.xml.rels');
        self::assertTrue((new \DOMDocument())->loadXML($rels), 'workbook.xml.rels not well-formed');
        self::assertStringContainsString('Target="vbaProject.bin" Type="http://schemas.microsoft.com/office/2006/relationships/vbaProject"', $rels);

        $workbook = $this->read('xl/workbook.xml');
        self::assertTrue((new \DOMDocument())->loadXML($workbook), 'workbook.xml not well-formed');
        self::assertStringContainsString('<workbookPr codeName="ThisWorkbook"/>', $workbook);
        self::assertTrue(strpos($workbook, '<workbookPr') < strpos($workbook, '<sheets>'), 'workbookPr must precede sheets');

        // Default code name SheetN for a sheet without one, the given one otherwise
        $sheet1 = $this->read('xl/worksheets/sheet1.xml');
        self::assertTrue((new \DOMDocument())->loadXML($sheet1), 'sheet1.xml not well-formed');
        self::assertStringContainsString('<sheetPr codeName="Sheet1">', $sheet1);
        self::assertStringContainsString('<sheetPr codeName="shForm">', $this->read('xl/worksheets/sheet2.xml'));
    }

    public function testWithoutVbaProjectNothingChanges(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true);
        self::assertFalse($zip->locateName('xl/vbaProject.bin'));
        $zip->close();

        self::assertStringContainsString('spreadsheetml.sheet.main+xml', $this->read('[Content_Types].xml'));
        self::assertStringNotContainsString('vbaProject', $this->read('xl/_rels/workbook.xml.rels'));
        self::assertStringNotContainsString('<workbookPr', $this->read('xl/workbook.xml'));
        self::assertStringNotContainsString('<sheetPr', $this->read('xl/worksheets/sheet1.xml'));
    }

    public function testRejectsABinaryThatIsNotAVbaProject(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);

        $this->expectException(\InvalidArgumentException::class);
        $writer->setVbaProject('PK not an OLE file');
    }

    public function testRejectsAnInvalidSheetCodeName(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);

        $this->expectException(\InvalidArgumentException::class);
        $writer->getCurrentSheet()->setCodeName('1 invalid');
    }

    private function read(string $innerPath): string
    {
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true, 'cannot open produced xlsm');
        $contents = $zip->getFromName($innerPath);
        $zip->close();
        self::assertIsString($contents, "missing $innerPath");

        return $contents;
    }
}
