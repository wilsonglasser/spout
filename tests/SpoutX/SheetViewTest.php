<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\XLSX\Entity\SheetView;

/**
 * Locks the XLSX <sheetViews> feature (freeze panes, zoom, gridlines) ported
 * from OpenSpout v5. No golden oracle exists for this feature, so the produced
 * sheet XML is also checked for well-formedness and schema ordering.
 */
final class SheetViewTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-sheetview';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/sheetview.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testFreezeFirstRow(): void
    {
        $xml = $this->write(function ($sheet): void {
            $sheet->setSheetView((new SheetView())->setFreezeRow(2));
        });

        $this->assertWellFormed($xml);
        self::assertStringContainsString('<sheetViews><sheetView ', $xml);
        self::assertStringContainsString('<pane xSplit="0" ySplit="1" topLeftCell="A2" activePane="bottomRight" state="frozen"/>', $xml);

        // sheetViews must precede sheetData
        self::assertTrue(strpos($xml, '<sheetViews>') < strpos($xml, '<sheetData>'), 'sheetViews must precede sheetData');
    }

    public function testFreezeFirstColumnOnlyProducesValidPane(): void
    {
        // freezeRow left at its default 0 — must still yield a valid pane (no ySplit="-1"/"B0")
        $xml = $this->write(function ($sheet): void {
            $sheet->setSheetView((new SheetView())->setFreezeColumn('B'));
        });

        $this->assertWellFormed($xml);
        self::assertStringContainsString('<pane xSplit="1" ySplit="0" topLeftCell="B1" activePane="bottomRight" state="frozen"/>', $xml);
    }

    public function testZoomAndGridlinesWithoutFreezeEmitsNoPane(): void
    {
        $xml = $this->write(function ($sheet): void {
            $sheet->setSheetView((new SheetView())->setZoomScale(150)->setShowGridLines(false));
        });

        $this->assertWellFormed($xml);
        self::assertStringContainsString('zoomScale="150"', $xml);
        self::assertStringContainsString('showGridLines="false"', $xml);
        self::assertStringNotContainsString('<pane ', $xml);
    }

    public function testNoSheetViewEmitsNothing(): void
    {
        $xml = $this->write(static function ($sheet): void {
        });

        self::assertStringNotContainsString('<sheetViews>', $xml);
    }

    private function write(callable $configure): string
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $configure($writer->getCurrentSheet());
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a', 'b']));
        $writer->close();

        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true, 'cannot open produced xlsx');
        $contents = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        self::assertIsString($contents);

        return $contents;
    }

    private function assertWellFormed(string $xml): void
    {
        $dom = new \DOMDocument();
        self::assertTrue($dom->loadXML($xml), 'produced sheet XML is not well-formed');
    }
}
