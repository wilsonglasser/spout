<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\XLSX\Entity\HeaderFooter;
use SpoutX\Writer\XLSX\Entity\PageMargin;
use SpoutX\Writer\XLSX\Entity\PageOrientation;
use SpoutX\Writer\XLSX\Entity\PageSetup;
use SpoutX\Writer\XLSX\Entity\PaperSize;

/**
 * Locks the XLSX print page-setup feature (orientation, paper size, margins,
 * fit-to-page and header/footer) ported from OpenSpout v5. Because there is no
 * golden oracle for this feature, the test also asserts the produced sheet XML
 * is well-formed and that the print blocks appear in CT_Worksheet schema order.
 */
final class PageSetupTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-pagesetup';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/pagesetup.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testPageSetupIsEmittedInSchemaOrderAndWellFormed(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);

        $sheet = $writer->getCurrentSheet();
        $sheet->setPageSetup(new PageSetup(PageOrientation::Landscape, PaperSize::A4, fitToHeight: 1, fitToWidth: 2));
        $sheet->setPageMargin(new PageMargin(1.0, 0.5, 1.0, 0.5, 0.25, 0.25));
        // "&L" style codes contain '&', which must be XML-escaped in the file
        $sheet->setHeaderFooter(new HeaderFooter(oddHeader: '&LLeft&CTitle', oddFooter: '&RPage &P'));

        $writer->addRow(WriterEntityFactory::createRowFromArray(['a', 'b']));
        $writer->close();

        $xml = $this->readFromXlsx('xl/worksheets/sheet1.xml');

        // Well-formed XML (there is no golden oracle for this feature)
        $dom = new \DOMDocument();
        self::assertTrue($dom->loadXML($xml), 'produced sheet XML is not well-formed');

        // fit-to-page hint sits in <sheetPr>, before <sheetData>
        self::assertStringContainsString('<pageSetUpPr fitToPage="true"/>', $xml);

        self::assertStringContainsString('<pageMargins top="1" right="0.5" bottom="1" left="0.5" header="0.25" footer="0.25"/>', $xml);
        self::assertStringContainsString('<pageSetup orientation="landscape" paperSize="9" fitToHeight="1" fitToWidth="2"/>', $xml);
        self::assertStringContainsString('<oddHeader>&amp;LLeft&amp;CTitle</oddHeader>', $xml);
        self::assertStringContainsString('<oddFooter>&amp;RPage &amp;P</oddFooter>', $xml);

        // CT_Worksheet order: sheetData ... pageMargins < pageSetup < headerFooter
        $posData = strpos($xml, '</sheetData>');
        $posMargins = strpos($xml, '<pageMargins');
        $posSetup = strpos($xml, '<pageSetup ');
        $posHF = strpos($xml, '<headerFooter');
        self::assertNotFalse($posData);
        self::assertTrue($posData < $posMargins, 'pageMargins must come after sheetData');
        self::assertTrue($posMargins < $posSetup, 'pageMargins must precede pageSetup');
        self::assertTrue($posSetup < $posHF, 'pageSetup must precede headerFooter');
    }

    public function testNoPageSetupEmitsNothing(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $xml = $this->readFromXlsx('xl/worksheets/sheet1.xml');
        self::assertStringNotContainsString('<pageMargins', $xml);
        self::assertStringNotContainsString('<pageSetup', $xml);
        self::assertStringNotContainsString('<headerFooter', $xml);
        self::assertStringNotContainsString('<sheetPr>', $xml);
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
