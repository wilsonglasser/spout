<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\CellType;
use SpoutX\Common\Entity\ColumnDimension;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\Style\StyleBuilder;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

/**
 * Locks the two escape hatches added for template generation:
 *
 * - {@see CellType::Text}: an explicit "always a string" cell type. Unlike
 *   CellType::String, the writer never coerces a numeric-looking value to a
 *   number, so digit-only identifiers ("0123", tax ids) keep their leading
 *   zeros.
 * - {@see ColumnDimension} column-level style: written as the style="N"
 *   attribute of <col>, giving the whole column a default style (typically a
 *   number format like "@" or "0.######") without touching any cell.
 */
final class TextTypeAndColumnStyleTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-texttype';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/texttype.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testTextTypeKeepsNumericLookingValueAsString(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->setShouldUseInlineStrings(false)->openToFile($this->tmpFile);
        $writer->addRow([[
            [CellType::Text, '0123'],
            [CellType::String, '0123'],
        ]]);
        $writer->close();

        [$sheetXml, $sharedStrings] = $this->readXml();

        // A1 (Text): shared string, leading zero preserved.
        self::assertStringContainsString('<t>0123</t>', $sharedStrings);
        self::assertMatchesRegularExpression('/<c r="A1"[^>]*t="s">/', $sheetXml);

        // B1 (String): numeric coercion still applies — unchanged legacy behavior.
        self::assertMatchesRegularExpression('/<c r="B1"[^>]*t="n"><v>0123<\/v>/', $sheetXml);
    }

    public function testTextTypeWithEmptyValueBehavesAsEmptyCell(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->setShouldUseInlineStrings(false)->openToFile($this->tmpFile);
        $writer->addRow([[
            [CellType::Text, ''],
            [CellType::Text, 'x'],
        ]]);
        $writer->close();

        [$sheetXml] = $this->readXml();

        self::assertStringNotContainsString('<c r="A1"', $sheetXml);
        self::assertMatchesRegularExpression('/<c r="B1"[^>]*t="s">/', $sheetXml);
    }

    public function testCellEntityExposesTextType(): void
    {
        $cell = new Cell('0123');
        $cell->setType(CellType::Text);

        self::assertTrue($cell->isText());
        self::assertFalse($cell->isString());
    }

    public function testColumnDimensionStyleIsWrittenOnColElement(): void
    {
        $textFormat = (new StyleBuilder)->setFormat('@')->build();

        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $sheet = $writer->getCurrentSheet();
        $sheet->addColumnDimension(new ColumnDimension('A', 20, style: $textFormat));
        $sheet->addColumnDimension(new ColumnDimension('B', 15));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a', 'b']));
        $writer->close();

        [$sheetXml] = $this->readXml();
        $stylesXml = $this->readZipEntry('xl/styles.xml');

        // Column A carries a style id; column B does not.
        self::assertMatchesRegularExpression('/<col [^>]*min="1"[^>]*style="(\d+)"/', $sheetXml);
        preg_match('/<col [^>]*min="1"[^>]*style="(\d+)"/', $sheetXml, $matches);
        $styleId = (int) $matches[1];
        self::assertGreaterThan(0, $styleId);
        self::assertDoesNotMatchRegularExpression('/<col [^>]*min="2"[^>]*style="/', $sheetXml);

        // The registered style maps "@" to the builtin text format (numFmtId 49).
        self::assertStringContainsString('numFmtId="49"', $stylesXml);
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
