<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Reader\Common\Creator\ReaderEntityFactory;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * Locks reading of merge-cell ranges from a sheet (ported from OpenSpout v5).
 */
final class ReadMergeCellsTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-readmerge';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/readmerge.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testReadsMergeCellRanges(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $sheet = $writer->getCurrentSheet();
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A3:A5');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Title', 'b', 'c']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['x', 'y', 'z']));
        $writer->close();

        $reader = ReaderEntityFactory::createReader(Type::XLSX);
        $reader->open($this->tmpFile);
        $merges = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $merges = $sheet->getMergeCells();
            break;
        }
        $reader->close();

        self::assertContains('A1:C1', $merges);
        self::assertContains('A3:A5', $merges);
        self::assertCount(2, $merges);
    }

    public function testNoMergesReturnsEmptyArray(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a', 'b']));
        $writer->close();

        $reader = ReaderEntityFactory::createReader(Type::XLSX);
        $reader->open($this->tmpFile);
        $merges = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            $merges = $sheet->getMergeCells();
            break;
        }
        $reader->close();

        self::assertSame([], $merges);
    }
}
