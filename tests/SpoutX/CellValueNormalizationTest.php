<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Entity\CellType;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * Locks the defensive normalization in WorksheetManager::getCellXML() for
 * array-shorthand cells. Under strict_types, callers passing a raw object
 * (e.g. Carbon) as CellType::String used to fatal inside preg_match(), and a
 * non-DateTime as CellType::Date used to fatal in DateFormatHelper. The writer
 * now coerces instead of crashing the whole export.
 */
final class CellValueNormalizationTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-normalization';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/normalization.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testStringTypeWithDateTimeValueIsFormattedNotFatal(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->setShouldUseInlineStrings(false)->openToFile($this->tmpFile);
        $writer->addRow([[
            [CellType::String, new \DateTimeImmutable('2026-08-11 14:30:00')],
        ]]);
        $writer->close();

        [$sheetXml, $sharedStrings] = $this->readXml();

        self::assertStringContainsString('<t>2026-08-11 14:30:00</t>', $sharedStrings);
        self::assertMatchesRegularExpression('/<c r="A1"[^>]*t="s">/', $sheetXml);
    }

    public function testStringTypeWithScalarAndArrayValuesDoNotFatal(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->setShouldUseInlineStrings(false)->openToFile($this->tmpFile);
        $writer->addRow([[
            [CellType::String, 42],
            [CellType::String, 1.5],
            [CellType::String, ['a', 'b']],
        ]]);
        $writer->close();

        [$sheetXml, $sharedStrings] = $this->readXml();

        // Numeric-looking values keep the legacy numeric coercion.
        self::assertMatchesRegularExpression('/<c r="A1"[^>]*t="n"><v>42<\/v>/', $sheetXml);
        self::assertMatchesRegularExpression('/<c r="B1"[^>]*t="n"><v>1.5<\/v>/', $sheetXml);
        // Arrays degrade to their JSON representation instead of fataling.
        self::assertStringContainsString('[&quot;a&quot;,&quot;b&quot;]', $sharedStrings);
    }

    public function testDateTypeWithNonDateTimeValueDegradesToString(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->setShouldUseInlineStrings(false)->openToFile($this->tmpFile);
        $writer->addRow([[
            [CellType::Date, '11/08/2026'],
            [CellType::Date, new \DateTimeImmutable('2026-08-11')],
        ]]);
        $writer->close();

        [$sheetXml, $sharedStrings] = $this->readXml();

        // String value: written as a plain string cell, not a fatal.
        self::assertStringContainsString('<t>11/08/2026</t>', $sharedStrings);
        self::assertMatchesRegularExpression('/<c r="A1"[^>]*t="s">/', $sheetXml);
        // Real DateTime: still written as an Excel date serial.
        self::assertMatchesRegularExpression('/<c r="B1"[^>]*t="n"><v>4/', $sheetXml);
    }

    /** @return array{0: string, 1: string} sheet1 XML and sharedStrings XML */
    private function readXml(): array
    {
        return [
            $this->readZipEntry('xl/worksheets/sheet1.xml'),
            $this->readZipEntry('xl/sharedStrings.xml'),
        ];
    }

    private function readZipEntry(string $entry): string
    {
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->tmpFile) === true, 'cannot open produced xlsx');
        $contents = $zip->getFromName($entry);
        $zip->close();

        return $contents === false ? '' : $contents;
    }
}
