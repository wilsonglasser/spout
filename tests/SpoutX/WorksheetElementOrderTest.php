<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Entity\ColumnDimension;
use SpoutX\Common\Type;
use SpoutX\Reader\Common\Creator\ReaderEntityFactory;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\Common\Entity\Comment;
use SpoutX\Writer\XLSX\Entity\DataValidation;
use SpoutX\Writer\XLSX\Entity\HeaderFooter;
use SpoutX\Writer\XLSX\Entity\PageMargin;
use SpoutX\Writer\XLSX\Entity\PageOrientation;
use SpoutX\Writer\XLSX\Entity\PageSetup;
use SpoutX\Writer\XLSX\Entity\PaperSize;
use SpoutX\Writer\XLSX\Entity\SheetView;

/**
 * Integration guard: a single worksheet exercising every child element the
 * writer can emit (sheetPr, sheetViews, cols, sheetData, autoFilter, mergeCells,
 * dataValidations, hyperlinks, pageMargins/pageSetup/headerFooter, legacyDrawing)
 * must be well-formed AND appear in the strict CT_Worksheet order, and still
 * read back.
 */
final class WorksheetElementOrderTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-order';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/order.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testAllWorksheetElementsAreWellFormedAndInSchemaOrder(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);

        $sheet = $writer->getCurrentSheet();
        $sheet->setPageSetup(new PageSetup(PageOrientation::Landscape, PaperSize::A4, fitToHeight: 1));
        $sheet->setPageMargin(new PageMargin());
        $sheet->setHeaderFooter(new HeaderFooter(oddHeader: '&CReport'));
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->addColumnDimension(new ColumnDimension('A', 30));
        $sheet->setAutoFilter('A1:C1');
        $sheet->mergeCells('A1:B1');
        $sheet->addDataValidation(DataValidation::listFromValues('C2:C10', ['Yes', 'No']));
        $sheet->addHyperlink('A2', 'https://example.com');
        $sheet->addComment(new Comment('B2', 'a note', 'Author'));

        $writer->addRow(WriterEntityFactory::createRowFromArray(['Title', 'b', 'c']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['link', 'note', 'x']));
        $writer->close();

        $xml = $this->read('xl/worksheets/sheet1.xml');

        $dom = new \DOMDocument();
        self::assertTrue($dom->loadXML($xml), 'combined sheet XML is not well-formed');

        // The worksheet rels (comments + hyperlink) must also be well-formed
        $rels = $this->read('xl/worksheets/_rels/sheet1.xml.rels');
        self::assertTrue((new \DOMDocument())->loadXML($rels), 'combined sheet rels not well-formed');

        $order = [
            '<sheetPr>',
            '<sheetViews>',
            '<cols>',
            '<sheetData>',
            '<autoFilter',
            '<mergeCells>',
            '<dataValidations',
            '<hyperlinks>',
            '<pageMargins',
            '<pageSetup ',
            '<headerFooter',
            '<legacyDrawing',
        ];
        $previous = -1;
        foreach ($order as $needle) {
            $pos = strpos($xml, $needle);
            self::assertNotFalse($pos, "missing element $needle");
            self::assertGreaterThan($previous, $pos, "element $needle is out of CT_Worksheet order");
            $previous = $pos;
        }
    }

    public function testCombinedFileStillReadsBack(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        $sheet->addDataValidation(DataValidation::listFromValues('C2:C10', ['Yes', 'No']));
        $sheet->addHyperlink('A2', 'https://example.com');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a', 'b', 'c']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['d', 'e', 'f']));
        $writer->close();

        $reader = ReaderEntityFactory::createReader(Type::XLSX);
        $reader->open($this->tmpFile);
        $cellCount = 0;
        foreach ($reader->getSheetIterator() as $s) {
            foreach ($s->getRowIterator() as $row) {
                $cellCount += count($row->getCells());
            }
        }
        $reader->close();

        self::assertSame(6, $cellCount, 'combined-feature file did not read back its rows');
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
