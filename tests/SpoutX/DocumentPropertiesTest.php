<?php

declare(strict_types=1);

namespace SpoutX\Test;

use PHPUnit\Framework\TestCase;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\XLSX\Entity\DocumentProperties;

/**
 * Locks the XLSX document-properties feature (docProps/core.xml + app.xml).
 */
final class DocumentPropertiesTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/spout-docprops';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->tmpFile = $dir . '/docprops.xlsx';
        @unlink($this->tmpFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testSetsCoreAndAppProperties(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->setDocumentProperties(new DocumentProperties(
            title: 'Q1 Report & Review',
            creator: 'Kelvin',
            keywords: 'finance,q1',
            description: 'A description',
            application: 'TBL Manager',
        ));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $core = $this->read('docProps/core.xml');
        $app = $this->read('docProps/app.xml');

        self::assertTrue((new \DOMDocument())->loadXML($core), 'core.xml not well-formed');
        self::assertTrue((new \DOMDocument())->loadXML($app), 'app.xml not well-formed');

        self::assertStringContainsString('<dc:title>Q1 Report &amp; Review</dc:title>', $core);
        self::assertStringContainsString('<dc:creator>Kelvin</dc:creator>', $core);
        self::assertStringContainsString('<cp:keywords>finance,q1</cp:keywords>', $core);
        self::assertStringContainsString('<dc:description>A description</dc:description>', $core);
        // untouched fields are not emitted
        self::assertStringNotContainsString('<dc:subject>', $core);

        self::assertStringContainsString('<Application>TBL Manager</Application>', $app);
    }

    public function testDefaultCoreXmlIsStillValidWithoutProperties(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $core = $this->read('docProps/core.xml');
        self::assertTrue((new \DOMDocument())->loadXML($core));
        self::assertStringContainsString('<dcterms:created', $core);
        self::assertStringContainsString('<cp:revision>0</cp:revision>', $core);
        self::assertStringNotContainsString('<dc:title>', $core);
    }

    public function testCustomProperties(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->setDocumentProperties(new DocumentProperties(
            title: 'X',
            customProperties: ['Department' => 'Finance', 'Reviewed' => 'yes & done'],
        ));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $custom = $this->read('docProps/custom.xml');
        $types = $this->read('[Content_Types].xml');
        $rels = $this->read('_rels/.rels');

        self::assertTrue((new \DOMDocument())->loadXML($custom), 'custom.xml not well-formed');
        self::assertStringContainsString('pid="2" name="Department"><vt:lpwstr>Finance</vt:lpwstr>', $custom);
        self::assertStringContainsString('pid="3" name="Reviewed"><vt:lpwstr>yes &amp; done</vt:lpwstr>', $custom);

        // wired into content types and package rels
        self::assertStringContainsString('PartName="/docProps/custom.xml"', $types);
        self::assertStringContainsString('Id="rIdCustom"', $rels);
        self::assertStringContainsString('Target="docProps/custom.xml"', $rels);
    }

    public function testNoCustomPropertiesOmitsCustomXml(): void
    {
        $writer = WriterEntityFactory::createWriter(Type::XLSX);
        $writer->openToFile($this->tmpFile);
        $writer->setDocumentProperties(new DocumentProperties(title: 'X'));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['a']));
        $writer->close();

        $zip = new \ZipArchive();
        $zip->open($this->tmpFile);
        self::assertFalse($zip->getFromName('docProps/custom.xml'), 'custom.xml should not exist');
        $rels = $zip->getFromName('_rels/.rels');
        $zip->close();
        self::assertStringNotContainsString('rIdCustom', (string) $rels);
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
