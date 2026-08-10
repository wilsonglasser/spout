<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\XLSX\Entity\WorkbookProtection;

/**
 * Locks the XLSX workbook-protection feature (lockStructure/lockWindows +
 * optional password) emitted before <sheets> in workbook.xml.
 */
final class WorkbookProtectionTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-wbprotection';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/wbprotection.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testWorkbookProtectionWithPassword(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->setWorkbookProtection(new WorkbookProtection(password: 'test', lockStructure: true));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $workbook = $this->read('xl/workbook.xml');

        self::assertTrue((new \DOMDocument())->loadXML($workbook), 'workbook.xml not well-formed');
        self::assertStringContainsString('<workbookProtection ', $workbook);
        self::assertStringContainsString('lockStructure="true"', $workbook);
        self::assertStringContainsString('workbookPassword="CBEB"', $workbook);

        // must precede <sheets>
        self::assertTrue(strpos($workbook, '<workbookProtection') < strpos($workbook, '<sheets>'), 'workbookProtection must precede sheets');
    }

    public function testNoWorkbookProtectionEmitsNothing(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $workbook = $this->read('xl/workbook.xml');
        self::assertStringNotContainsString('<workbookProtection', $workbook);
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
