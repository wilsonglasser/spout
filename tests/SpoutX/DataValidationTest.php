<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\XLSX\Entity\DataValidation;
use SpoutX\Writer\XLSX\Entity\ValidationOperator;
use SpoutX\Writer\XLSX\Entity\ValidationType;

/**
 * Locks the XLSX data-validation feature (dropdown lists and numeric/date
 * constraints) modelled on OpenSpout v5. No golden oracle exists, so the sheet
 * XML is also asserted well-formed and in CT_Worksheet schema order.
 */
final class DataValidationTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-datavalidation';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/datavalidation.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testDropdownListFromValues(): void
    {
        $xml = $this->write(function ($sheet): void {
            $sheet->addDataValidation(DataValidation::listFromValues('A2:A100', ['Yes', 'No', 'Maybe']));
        });

        $this->assertWellFormed($xml);
        self::assertStringContainsString('<dataValidations count="1">', $xml);
        self::assertStringContainsString('<dataValidation type="list"', $xml);
        self::assertStringContainsString('sqref="A2:A100"', $xml);
        self::assertStringContainsString('<formula1>"Yes,No,Maybe"</formula1>', $xml);
    }

    public function testWholeNumberConstraintWithOperatorAndFormula2(): void
    {
        $xml = $this->write(function ($sheet): void {
            $sheet->addDataValidation(new DataValidation(
                sqref: 'B2:B10',
                type: ValidationType::Whole,
                formula1: '1',
                formula2: '100',
                operator: ValidationOperator::Between,
                error: 'Enter 1-100',
                errorTitle: 'Bad value',
            ));
        });

        $this->assertWellFormed($xml);
        self::assertStringContainsString('<dataValidation type="whole" operator="between"', $xml);
        self::assertStringContainsString('error="Enter 1-100"', $xml);
        self::assertStringContainsString('errorTitle="Bad value"', $xml);
        self::assertStringContainsString('<formula1>1</formula1><formula2>100</formula2>', $xml);
    }

    public function testListValueWithCommaIsRejected(): void
    {
        $this->expectException(\SpoutX\Common\Exception\InvalidArgumentException::class);
        DataValidation::listFromValues('A1:A2', ['a,b']);
    }

    public function testDataValidationsPrecedeHyperlinks(): void
    {
        $xml = $this->write(function ($sheet): void {
            $sheet->addDataValidation(DataValidation::listFromValues('A2:A2', ['x']));
            $sheet->addHyperlink('B1', 'https://example.com');
        });

        $this->assertWellFormed($xml);
        self::assertTrue(strpos($xml, '<dataValidations') < strpos($xml, '<hyperlinks>'), 'dataValidations must precede hyperlinks');
    }

    public function testNoValidationEmitsNothing(): void
    {
        $xml = $this->write(static function ($sheet): void {
        });
        self::assertStringNotContainsString('<dataValidations', $xml);
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
