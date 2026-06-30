<?php

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Reader\Common\Creator\ReaderEntityFactory;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * End-to-end safety net: write an XLSX file and read it back.
 * Proves the XLSX read+write path works on PHP 8.4 before any refactor.
 */
final class SmokeRoundtripTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-smoke';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/roundtrip.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testWriteThenReadBackXlsx(): void
    {
        $expected = [
            ['Name', 'Age', 'City'],
            ['Alice', '30', 'Lisbon'],
            ['Bob', '25', 'Porto'],
        ];

        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        foreach ($expected as $rowValues) {
            $writer->addRow(WriterEntityFactory::createRowFromArray($rowValues));
        }
        $writer->close();

        self::assertFileExists($this->tmpFile);

        $reader = ReaderEntityFactory::createReader(Type::XLSX);
        $reader->open($this->tmpFile);

        $actual = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $actual[] = array_map(static fn ($v) => (string) $v, $row->toArray());
            }
        }
        $reader->close();

        self::assertSame($expected, $actual);
    }
}
