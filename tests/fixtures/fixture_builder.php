<?php

/**
 * Shared characterization fixture.
 *
 * Builds one XLSX exercising the full matrix of cell types + a rich Style +
 * every extra feature of the fork. Used by:
 *   - tests/fixtures/dump_golden.php  (run at the oracle commit to capture golden XML)
 *   - tests/SpoutX/CharacterizationTest.php  (asserts HEAD produces identical XML)
 *
 * IMPORTANT: only use the public API that exists at BOTH the oracle commit and
 * HEAD, and keep every value deterministic (no timestamps, no randomness).
 */

use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\ColumnDimension;
use SpoutX\Common\Entity\Style\Border;
use SpoutX\Common\Entity\Style\Color;
use SpoutX\Common\Entity\Style\Style;
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\Style\BorderBuilder;
use SpoutX\Writer\Common\Creator\Style\StyleBuilder;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\Common\Entity\Comment;

function spoutx_build_fixture_xlsx(string $path): void
{
    $border = (new BorderBuilder())
        ->setBorderTop(Color::RED, Border::WIDTH_THIN, Border::STYLE_SOLID)
        ->setBorderBottom(Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_DASHED)
        ->build();

    $headerStyle = (new StyleBuilder())
        ->setFontBold()
        ->setFontSize(14)
        ->setFontName('Calibri')
        ->setFontColor(Color::WHITE)
        ->setBackgroundColor(Color::DARK_RED)
        ->setBorder($border)
        ->setHorizontalAlign(Style::ALIGN_MIDDLE)
        ->setVerticalAlign(Style::ALIGN_MIDDLE)
        ->setShouldWrapText()
        ->build();

    $moneyStyle = (new StyleBuilder())
        ->setFormat('#,##0.00')
        ->setHorizontalAlign(Style::ALIGN_RIGHT)
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
