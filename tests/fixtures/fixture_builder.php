<?php

/**
 * Shared characterization fixture.
 *
 * Builds one XLSX exercising the full matrix of cell types + a rich Style +
 * every extra feature of the fork. Used by:
 *   - tests/fixtures/dump_golden.php  (run at the oracle commit to capture golden XML)
 *   - tests/SpoutX/CharacterizationTest.php  (asserts HEAD produces identical XML)
 *
 * The golden fixtures under tests/fixtures/golden/ are the locked oracle (first
 * captured at commit f39c910). This builder tracks the CURRENT public API and
 * must keep producing byte-identical XML (modulo line endings). Keep every value
 * deterministic (no timestamps, no randomness).
 */

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\ColumnDimension;
use SpoutX\Common\Entity\Style\BorderStyle;
use SpoutX\Common\Entity\Style\BorderWidth;
use SpoutX\Common\Entity\Style\CellAlignment;
use SpoutX\Common\Entity\Style\CellVerticalAlignment;
use SpoutX\Common\Entity\Style\Color;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\Style\BorderBuilder;
use SpoutX\Writer\Common\Creator\Style\StyleBuilder;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\Common\Entity\Comment;

function spoutx_build_fixture_xlsx(string $path): void
{
    $border = (new BorderBuilder())
        ->setBorderTop(Color::RED, BorderWidth::Thin, BorderStyle::Solid)
        ->setBorderBottom(Color::BLACK, BorderWidth::Medium, BorderStyle::Dashed)
        ->build();

    $headerStyle = (new StyleBuilder())
        ->setFontBold()
        ->setFontSize(14)
        ->setFontName('Calibri')
        ->setFontColor(Color::WHITE)
        ->setBackgroundColor(Color::DARK_RED)
        ->setBorder($border)
        ->setHorizontalAlign(CellAlignment::Center)
        ->setVerticalAlign(CellVerticalAlignment::Center)
        ->setShouldWrapText()
        ->build();

    $moneyStyle = (new StyleBuilder())
        ->setFormat('#,##0.00')
        ->setHorizontalAlign(CellAlignment::Right)
        ->build();

    $writer = WriterEntityFactory::createWriter(Type::XLSX);
    $writer->openToFile($path);

    $sheet = $writer->getCurrentSheet();
    $sheet->mergeCells('A1:E1');
    $sheet->setAutoFilter('A2:E2');
    $sheet->addColumnDimension(new ColumnDimension('A', 30));
    $sheet->addColumnDimension(new ColumnDimension('B', -1, true));
    $sheet->addComment(new Comment('A2', 'A characterization comment', 'SpoutX'));

    // Header row (styled, merged)
    $writer->addRow(WriterEntityFactory::createRow(
        [new Cell('Characterization Header')],
        $headerStyle
    ));

    // Column titles
    $writer->addRow(WriterEntityFactory::createRowFromArray(
        ['String', 'Numeric', 'Boolean', 'Date', 'Formula']
    ));

    // One row per cell type + a formula cell with a precomputed value
    $date = new \DateTime('2020-01-15 09:30:00', new \DateTimeZone('UTC'));
    $formula = new Cell('=B4*2');
    $formula->setCalculatedValue('84');

    $writer->addRow(WriterEntityFactory::createRow([
        new Cell('Hello "world" & <friends>'),
        new Cell(42),
        new Cell(true),
        new Cell($date),
        $formula,
    ]));

    // Floats, false, empty cell, and a money-formatted numeric
    $writer->addRow(WriterEntityFactory::createRow([
        new Cell('Second'),
        new Cell(3.14159),
        new Cell(false),
        new Cell(''),
        new Cell(1234.5, $moneyStyle),
    ]));

    $writer->close();
}
