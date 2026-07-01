# SpoutX

**SpoutX** is a fast, low-memory PHP library to **read and write XLSX** spreadsheet files.

It is a modernized, XLSX-only fork of the (discontinued) [Spout](https://github.com/box/spout) library, rebuilt for **PHP 8.4+**. Unlike Spout and its successor OpenSpout, SpoutX keeps a **mutable API** on purpose — the reason this fork exists is to let you build up cells, rows and styles imperatively — while adding first-class support for merged cells, comments, formulas with precomputed values, column dimensions/auto-size, auto filters, row heights and custom number formats.

- **XLSX only** — CSV and ODS support has been removed to keep the library small and focused.
- **PHP 8.4+** — `declare(strict_types=1)` everywhere, backed enums, typed properties, constructor promotion.
- **Mutable by design** — entities (`Cell`, `Row`, `Style`, …) are built and mutated in place; no `readonly`/`withX` ceremony.
- **Streaming & scalable** — rows are written to disk as you go, so very large files stay within a small memory footprint.

---

## Requirements

- PHP **>= 8.4**
- Extensions: `ext-dom`, `ext-mbstring`, `ext-xmlreader`, `ext-zip`

## Installation

```bash
composer require wilsonglasser/spoutx
```

Root namespace is `SpoutX\` (no vendor prefix).

---

## Quick start — writing

```php
use SpoutX\Common\Type;
use SpoutX\Writer\Common\Creator\WriterEntityFactory;

$writer = WriterEntityFactory::createWriter(Type::XLSX);
$writer->openToFile('/path/to/file.xlsx');   // or ->openToBrowser('file.xlsx')

// Add rows from plain arrays…
$writer->addRow(WriterEntityFactory::createRowFromArray(['Name', 'Age', 'Active']));
$writer->addRow(WriterEntityFactory::createRowFromArray(['Alice', 30, true]));

// …or add several at once
$writer->addRows([
    WriterEntityFactory::createRowFromArray(['Bob', 25, false]),
    WriterEntityFactory::createRowFromArray(['Carol', 41, true]),
]);

$writer->close();
```

## Quick start — reading

```php
use SpoutX\Common\Type;
use SpoutX\Reader\Common\Creator\ReaderEntityFactory;

$reader = ReaderEntityFactory::createReader(Type::XLSX);
// shortcut that infers the type from the extension:
// $reader = ReaderEntityFactory::createReaderFromFile('/path/to/file.xlsx');

$reader->open('/path/to/file.xlsx');

foreach ($reader->getSheetIterator() as $sheet) {
    echo "Sheet: {$sheet->getName()}\n";
    foreach ($sheet->getRowIterator() as $row) {
        $values = $row->toArray();          // array of scalar cell values
        // or iterate cells: foreach ($row->getCells() as $cell) { $cell->getValue(); }
        print_r($values);
    }
}

$reader->close();
```

**Reader options** (call before `open()`):

```php
$reader->setShouldFormatDates(true);         // return formatted date strings instead of DateTime
$reader->setShouldPreserveEmptyRows(true);   // keep empty rows in the iteration
$reader->setTempFolder('/custom/tmp');       // XLSX only
```

---

## Cells and types

A `Cell` wraps a value; its type is detected automatically and exposed as the `CellType` enum.

```php
use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\CellType;

$cell = new Cell(42);
$cell->getType();        // CellType::Numeric
$cell->isNumeric();      // true
$cell->getValue();       // 42
```

`CellType` cases: `Numeric`, `String`, `Formula`, `Empty`, `Boolean`, `Date`, `Error`.

Supported values: `string`, `int`, `float`, `bool`, `\DateTime`/`\DateInterval`, `''`/`null` (empty), and formula strings starting with `=`.

---

## Styling

Build styles with `StyleBuilder` and apply them per row (via the row/factory) or per cell.

```php
use SpoutX\Writer\Common\Creator\WriterEntityFactory;
use SpoutX\Writer\Common\Creator\Style\StyleBuilder;
use SpoutX\Common\Entity\Style\Color;
use SpoutX\Common\Entity\Style\CellAlignment;
use SpoutX\Common\Entity\Style\CellVerticalAlignment;

$header = (new StyleBuilder())
    ->setFontBold()
    ->setFontSize(14)
    ->setFontName('Calibri')
    ->setFontColor(Color::WHITE)
    ->setBackgroundColor(Color::DARK_RED)
    ->setHorizontalAlign(CellAlignment::Center)
    ->setVerticalAlign(CellVerticalAlignment::Center)
    ->setShouldWrapText()
    ->build();

$writer->addRow(WriterEntityFactory::createRowFromArray(['Report'], $header));
```

`StyleBuilder` methods: `setFontBold()`, `setFontItalic()`, `setFontUnderline()`, `setFontStrikethrough()`, `setFontSize(int)`, `setFontName(string)`, `setFontColor(string)`, `setBackgroundColor(string)`, `setShouldWrapText(bool = true)`, `setShrinkToFit(bool = false)`, `setHorizontalAlign(CellAlignment)`, `setVerticalAlign(CellVerticalAlignment)`, `setBorder(Border)`, `setFormat(string)`, `setNumberFormat(NumberFormat)`, `setRowHeight(float)`.

**Alignment enums**

- `CellAlignment` (horizontal): `Left`, `Right`, `Center`, `General`
- `CellVerticalAlignment`: `Top`, `Center`, `Bottom`

**Colors**

Named constants (`Color::BLACK`, `WHITE`, `RED`, `DARK_RED`, `ORANGE`, `YELLOW`, `LIGHT_GREEN`, `GREEN`, `LIGHT_BLUE`, `BLUE`, `DARK_BLUE`, `PURPLE`) or build your own:

```php
$rgb = Color::rgb(255, 192, 0); // "FFC000"
```

**Borders**

```php
use SpoutX\Writer\Common\Creator\Style\BorderBuilder;
use SpoutX\Common\Entity\Style\Color;
use SpoutX\Common\Entity\Style\BorderWidth;
use SpoutX\Common\Entity\Style\BorderStyle;

$border = (new BorderBuilder())
    ->setBorderTop(Color::RED, BorderWidth::Thin, BorderStyle::Solid)
    ->setBorderBottom(Color::BLACK, BorderWidth::Medium, BorderStyle::Dashed)
    ->build();

$style = (new StyleBuilder())->setBorder($border)->build();
```

- `BorderWidth`: `Thin`, `Medium`, `Thick`
- `BorderStyle`: `None`, `Solid`, `Dashed`, `Dotted`, `Double`
- Sides: `setBorderTop()`, `setBorderRight()`, `setBorderBottom()`, `setBorderLeft()`

**Row height**

```php
$style = (new StyleBuilder())->setRowHeight(50)->build();
$writer->addRow(WriterEntityFactory::createRowFromArray(['Tall row'], $style));
```

**Number format**

```php
use SpoutX\Common\Entity\Style\NumberFormat;

$money = (new StyleBuilder())->setNumberFormat(new NumberFormat('#,##0.00'))->build();
// setFormat() is the shorthand: ->setFormat('#,##0.00')
```

---

## Extra features (why this fork exists)

These are only available in the XLSX writer. Get the current sheet with `$writer->getCurrentSheet()`.

**Merge cells**

```php
$writer->getCurrentSheet()->mergeCells('A1:E1');
```

**Auto filter**

```php
$writer->getCurrentSheet()->setAutoFilter('A2:E2');
```

**Column dimensions (width / auto-size / visibility)**

```php
use SpoutX\Common\Entity\ColumnDimension;

$sheet = $writer->getCurrentSheet();
$sheet->addColumnDimension(new ColumnDimension('A', 30));        // fixed width 30
$sheet->addColumnDimension(new ColumnDimension('B', -1, true));  // auto-size
// signature: new ColumnDimension(string|int $columnIndex = 'A', float $width = -1, bool $autoSize = false, bool $visible = true)
```

**Comments**

```php
use SpoutX\Writer\Common\Entity\Comment;

$sheet->addComment(new Comment('A2', 'A note', 'Author'));       // author is optional
```
`Comment` also exposes `setWidth()`, `setHeight()`, `setMarginLeft()`, `setMarginTop()`, `setVisible()` and `setStyle()`.

**Formulas with a precomputed value**

SpoutX does not evaluate formulas — you supply the value Excel should display until it recalculates.

```php
$formula = new Cell('=B4*2');
$formula->setCalculatedValue('84');
$writer->addRow(WriterEntityFactory::createRow([$formula]));
```

**Sheets**

```php
$sheet = $writer->getCurrentSheet();
$sheet->setName('Summary');
$sheet->setIsVisible(true);

$second = $writer->addNewSheetAndMakeItCurrent();  // returns the new Sheet
```

**Default row style & writer options**

```php
$writer->setDefaultRowStyle($someStyle);           // applied to rows without an explicit style
$writer->setShouldUseInlineStrings(true);          // XLSX: inline vs shared strings
$writer->setTempFolder('/custom/tmp');
```

---

## Page setup, views, hyperlinks & data validation

These XLSX features were ported from OpenSpout v5 and adapted to SpoutX's mutable,
per-sheet model. They are configured on the sheet (`$writer->getCurrentSheet()`).

**Print page setup**

```php
use SpoutX\Writer\XLSX\Entity\PageSetup;
use SpoutX\Writer\XLSX\Entity\PageMargin;
use SpoutX\Writer\XLSX\Entity\HeaderFooter;
use SpoutX\Writer\XLSX\Entity\PageOrientation;
use SpoutX\Writer\XLSX\Entity\PaperSize;

$sheet->setPageSetup(new PageSetup(PageOrientation::Landscape, PaperSize::A4, fitToHeight: 1, fitToWidth: 1));
$sheet->setPageMargin(new PageMargin(top: 1.0, bottom: 1.0));
$sheet->setHeaderFooter(new HeaderFooter(oddHeader: '&CMy report', oddFooter: '&RPage &P of &N'));
```

**Freeze panes / sheet views**

```php
use SpoutX\Writer\XLSX\Entity\SheetView;

// Freeze the first (header) row:
$sheet->setSheetView((new SheetView())->setFreezeRow(2));
// Freeze the first column:  ->setFreezeColumn('B')
// Zoom / gridlines:         (new SheetView())->setZoomScale(150)->setShowGridLines(false)
```

**Hyperlinks**

```php
$sheet->addHyperlink('A1', 'https://example.com');
$sheet->addHyperlink('A2', 'mailto:hello@example.com');
```

**Data validation (dropdowns and constraints)**

```php
use SpoutX\Writer\XLSX\Entity\DataValidation;
use SpoutX\Writer\XLSX\Entity\ValidationType;
use SpoutX\Writer\XLSX\Entity\ValidationOperator;

// Dropdown from a fixed list (values must not contain commas):
$sheet->addDataValidation(DataValidation::listFromValues('A2:A100', ['Yes', 'No', 'Maybe']));
// Dropdown backed by a cell range:
$sheet->addDataValidation(DataValidation::listFromRange('B2:B100', 'Lists!$A$1:$A$10'));
// Whole-number constraint with a custom error message:
$sheet->addDataValidation(new DataValidation(
    sqref: 'C2:C100',
    type: ValidationType::Whole,
    formula1: '1',
    formula2: '100',
    operator: ValidationOperator::Between,
    errorTitle: 'Out of range',
    error: 'Enter a number from 1 to 100',
));
```

## Protection, visibility, properties & rich text

**Sheet & workbook protection** (locks *editing*, optionally password-guarded)

```php
use SpoutX\Writer\XLSX\Entity\SheetProtection;
use SpoutX\Writer\XLSX\Entity\WorkbookProtection;

$sheet->setSheetProtection(new SheetProtection(password: 'secret', lockSheet: true, lockSort: true));
$writer->setWorkbookProtection(new WorkbookProtection(password: 'secret', lockStructure: true)); // after openToFile()
```

**Tab visibility** (independent of protection). `lockStructure` above is what keeps a hidden tab hidden.

```php
use SpoutX\Writer\XLSX\Entity\SheetVisibility;

$sheet->setVisibility(SheetVisibility::Hidden);      // unhideable by the user via the UI? no
$sheet->setVisibility(SheetVisibility::VeryHidden);  // not unhideable from the UI (only via code)
// $sheet->setIsVisible(false) is kept and maps to Hidden
```

**Document properties**

```php
use SpoutX\Writer\XLSX\Entity\DocumentProperties;

$writer->setDocumentProperties(new DocumentProperties(
    title: 'Q1 Report', creator: 'Me', keywords: 'finance,q1', application: 'TBL Manager',
    customProperties: ['Department' => 'Finance', 'Reviewed' => 'yes'],
)); // after openToFile()
```

**Rich text** (multiple formats in one cell)

```php
use SpoutX\Common\Entity\Cell;
use SpoutX\Common\Entity\RichText;
use SpoutX\Common\Entity\TextRun;
use SpoutX\Common\Entity\Style\Color;

use SpoutX\Common\Entity\TextRunVerticalAlignment;

$cell = new Cell(new RichText(
    new TextRun('Hello ', bold: true, fontColor: Color::RED),
    new TextRun('world', italic: true, fontSize: 14, fontName: 'Calibri'),
    new TextRun('2', verticalAlignment: TextRunVerticalAlignment::Superscript),
));
```

**Reading merge cells**

```php
foreach ($reader->getSheetIterator() as $sheet) {
    $ranges = $sheet->getMergeCells();   // e.g. ['A1:C1', 'A3:A5']
}
```

---

## Migrating from Box\Spout / Spout

- Namespace changed from `Box\Spout\…` to **`SpoutX\…`**.
- Package is **`wilsonglasser/spoutx`**; minimum PHP is **8.4**.
- **CSV and ODS are gone** — use `Type::XLSX` only.
- Several constant sets are now **backed enums** (breaking, but type-safe):
  - `Style::ALIGN_*` → `CellAlignment` (horizontal) and `CellVerticalAlignment` (vertical).
  - Border name/style/width strings → `BorderName` / `BorderStyle` / `BorderWidth`.
  - Cell type ints → `CellType`.
  - So `setHorizontalAlign(Style::ALIGN_RIGHT)` becomes `setHorizontalAlign(CellAlignment::Right)`, and `setBorderTop($color, 'thin', 'solid')` becomes `setBorderTop($color, BorderWidth::Thin, BorderStyle::Solid)`.
- The API is still **mutable** — `new Cell(...)`, `->setValue()`, `->setStyle()`, builder setters returning `$this`, etc., all behave as before.

---

## Development

The dev environment runs in Docker (PHP 8.4 + ext-zip). Common tasks are wrapped in the `Makefile`:

```bash
make install    # composer install inside the container
make test       # run the PHPUnit suite
make cs         # php-cs-fixer dry-run (check)
make cs-fix     # php-cs-fixer fix
make shell      # open a shell in the container
```

The test suite includes a **golden-file characterization test** (`tests/SpoutX/CharacterizationTest.php`) that locks the exact XLSX XML output, plus a write→read roundtrip and a feature test covering the extras above.

---

## License

Licensed under the Apache License, Version 2.0. Originally Copyright Box, Inc.; fork maintained by Wilson Glasser.
