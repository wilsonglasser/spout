<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;

/**
 * Golden-file characterization test. The fixtures under tests/fixtures/golden
 * were captured at commit f39c910 (post strip/rename, pre any modernization),
 * which shares the public API with HEAD. Any divergence in the produced XML for
 * the full cell-type + style + extra-features matrix fails here.
 *
 * Regenerate the golden files only on an intentional output change:
 *   php tests/fixtures/dump_golden.php tests/fixtures/golden
 */
final class CharacterizationTest extends TestCase
{
    private const ENTRIES = [
        'xl/worksheets/sheet1.xml',
        'xl/styles.xml',
        'xl/sharedStrings.xml',
        'xl/comments1.xml',
    ];

    private string $xlsxPath;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../fixtures/fixture_builder.php';
        $this->xlsxPath = sys_get_temp_dir() . '/spoutx-characterization.xlsx';
        @unlink($this->xlsxPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->xlsxPath);
    }

    public function testProducedXmlMatchesGolden(): void
    {
        spoutx_build_fixture_xlsx($this->xlsxPath);

        $zip = new \ZipArchive();
        self::assertTrue($zip->open($this->xlsxPath) === true, 'cannot open produced xlsx');

        $goldenDir = __DIR__ . '/../fixtures/golden';
        $comparisons = 0;

        foreach (self::ENTRIES as $entry) {
            $goldenFile = $goldenDir . '/' . str_replace('/', '__', $entry);
            self::assertFileExists($goldenFile, "missing golden fixture for $entry");

            $actual = $zip->getFromName($entry);
            self::assertIsString($actual, "entry $entry is missing from the produced xlsx");

            // Line endings inside XLSX XML are insignificant (Excel reads either),
            // and the source was intentionally normalized to LF; compare content.
            self::assertSame(
                $this->normalizeEol(file_get_contents($goldenFile)),
                $this->normalizeEol($actual),
                "XML for $entry diverged from the golden fixture"
            );
            $comparisons++;
        }

        $zip->close();
        self::assertSame(count(self::ENTRIES), $comparisons);
    }

    private function normalizeEol(string $xml): string
    {
        return str_replace("\r\n", "\n", $xml);
    }
}
