<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\Common\Entity\Comment;

/**
 * Locks the XLSX external-hyperlink feature ported from OpenSpout v5, including
 * the reconciliation of the per-worksheet .rels file so that a sheet with BOTH
 * comments and hyperlinks gets a single rels file carrying both relationship
 * sets. No golden oracle exists, so XML well-formedness is also asserted.
 */
final class HyperlinkTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-hyperlink';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/hyperlink.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testHyperlinksEmitBlockAndMatchingRels(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $sheet = $writer->getCurrentSheet();
        $sheet->addHyperlink('A1', 'https://example.com/a?x=1&y=2');
        $sheet->addHyperlink('B1', 'mailto:test@example.com');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['site', 'email']));
        $writer->close();

        $sheetXml = $this->read('xl/worksheets/sheet1.xml');
        $relsXml = $this->read('xl/worksheets/_rels/sheet1.xml.rels');

        $this->assertWellFormed($sheetXml);
        $this->assertWellFormed($relsXml);

        self::assertStringContainsString('<hyperlink ref="A1" r:id="rId_hyperlink1"/>', $sheetXml);
        self::assertStringContainsString('<hyperlink ref="B1" r:id="rId_hyperlink2"/>', $sheetXml);

        // rels carry matching IDs, External mode, and the url XML-attribute-escaped
        self::assertStringContainsString('Id="rId_hyperlink1"', $relsXml);
        self::assertStringContainsString('Target="https://example.com/a?x=1&amp;y=2"', $relsXml);
        self::assertStringContainsString('TargetMode="External"', $relsXml);
        self::assertStringContainsString('Id="rId_hyperlink2"', $relsXml);
        self::assertStringContainsString('mailto:test@example.com', $relsXml);
    }

    public function testHyperlinksAndCommentsShareOneRelsFile(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $sheet = $writer->getCurrentSheet();
        $sheet->addComment(new Comment('A1', 'a note', 'Author'));
        $sheet->addHyperlink('A1', 'https://example.com');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['x']));
        $writer->close();

        $relsXml = $this->read('xl/worksheets/_rels/sheet1.xml.rels');
        $this->assertWellFormed($relsXml);

        // one rels file, both relationship kinds present
        self::assertStringContainsString('/relationships/comments', $relsXml);
        self::assertStringContainsString('/relationships/vmlDrawing', $relsXml);
        self::assertStringContainsString('Id="rId_hyperlink1"', $relsXml);
        self::assertStringContainsString('/relationships/hyperlink', $relsXml);
    }

    public function testNoHyperlinksEmitsNoBlock(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['x']));
        $writer->close();

        $sheetXml = $this->read('xl/worksheets/sheet1.xml');
        self::assertStringNotContainsString('<hyperlinks>', $sheetXml);

        $zip = new \ZipArchive();
        $zip->open($this->tmpFile);
        self::assertFalse($zip->getFromName('xl/worksheets/_rels/sheet1.xml.rels'), 'no rels file expected');
        $zip->close();
    }

    private function read(string $innerPath): string
    {
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true, 'cannot open produced xlsx');
        $contents = $zip->getFromName($innerPath);
        $zip->close();
        self::assertIsString($contents, "missing $innerPath in xlsx");

        return $contents;
    }

    private function assertWellFormed(string $xml): void
    {
        $dom = new \DOMDocument();
        self::assertTrue($dom->loadXML($xml), 'produced XML is not well-formed');
    }
}
