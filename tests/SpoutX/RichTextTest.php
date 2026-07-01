<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\CellType;
use SpoutX\Common\Entity\RichText;
use SpoutX\Common\Entity\Style\Color;
use SpoutX\Common\Entity\TextRun;
use SpoutX\Common\Type;
use SpoutX\Reader\Common\Creator\ReaderEntityFactory;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * Locks rich-text (multi-run inline string) cells ported from OpenSpout v5.
 */
final class RichTextTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-richtext';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/richtext.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testRichTextValueIsDetectedAndEmittedAsRuns(): void
    {
        $rich = new RichText(
            new TextRun('Hello ', bold: true, fontColor: Color::RED),
            new TextRun('world', italic: true, fontSize: 14, fontName: 'Calibri'),
        );

        $cell = new Cell($rich);
        self::assertSame(CellType::RichText, $cell->getType());
        self::assertTrue($cell->isRichText());

        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRow([$cell]));
        $writer->close();

        $xml = $this->read('xl/worksheets/sheet1.xml');
        self::assertTrue((new \DOMDocument())->loadXML($xml), 'sheet XML not well-formed');

        self::assertStringContainsString('t="inlineStr"><is>', $xml);
        self::assertStringContainsString('<r><rPr><b/><color rgb="FFFF0000"/></rPr><t xml:space="preserve">Hello </t></r>', $xml);
        self::assertStringContainsString('<r><rPr><i/><sz val="14"/><rFont val="Calibri"/></rPr><t xml:space="preserve">world</t></r>', $xml);
    }

    public function testRichTextRoundTripsAsConcatenatedText(): void
    {
        $rich = new RichText(new TextRun('Foo', bold: true), new TextRun('Bar', italic: true));

        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRow([new Cell($rich)]));
        $writer->close();

        $reader = ReaderEntityFactory::createReader(Type::XLSX);
        $reader->open($this->tmpFile);
        $value = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $value = $row->getCellAtIndex(0)?->getValue();
                break 2;
            }
        }
        $reader->close();

        self::assertSame('FooBar', $value, 'rich text should read back as its concatenated plain text');
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
