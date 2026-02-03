# Spout

## This version

This fork is a modified version of Spout v3.0, with the ability to add merge cells, comment, formula cells, column dimensions and auto filter for XLSX Writer.

> **Note:** The original box/spout project has been archived and is no longer maintained.
> This fork continues development with additional features.

## About

Spout is a PHP library to read and write spreadsheet files (CSV, XLSX and ODS), in a fast and scalable way.
Unlike other file readers or writers, it is capable of processing very large files, while keeping the memory usage really low (less than 3MB).

Join the community and come discuss Spout: [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/box/spout?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)


## Documentation

Sorry but I can't create a documentation for the new methods at the moment.

I will just list the small examples:

**Merge cells**
```php
$worksheet = $writer->getCurrentSheet();
$worksheet->mergeCells('A1:B1');
```

**Row Height**

```php
$rowStyle = (new StyleBuilder())
    ->setFontSize(10)
    ->setFontName('Arial')
    ->setRowHeight(50)
    ->build();
$writer->addRow(new Row([new Cell('Hello World')], $rowStyle));
```

**Column Dimension (Width)**

```php
// Specific size
$worksheet->addColumnDimension(
    new ColumnDimension('A', 50 )
);

// Autosize
$worksheet->addColumnDimension(new ColumnDimension(
    'A',
    -1,
    true
));
```

**Auto Filter**

Enable excel auto filters

```php
$worksheet = $writer->getCurrentSheet();
$worksheet->setAutoFilter('A1:Z1');
```

**Formula value**

Added support to show a calculated value for a formula (Will not calculate, you need to pass the correct value)
```php
$Cell = new Cell('=A1', null);
$Cell->setCalculatedValue('100.00');
```

**Number Format**

```php
$originalValue = 100.0;
$value = 'R$'.number_format($originalValue, 2,',','.');
$style = (new StyleBuilder())
        ->setFontSize(10)
        ->setFontName('Arial')
        ->setNumberFormat(new NumberFormat('0 - "' . $value . '"'))
        ->build();
$Cell = new Cell($value, $style);
// date
$styleMonthYear =  (new StyleBuilder())
    ->setFontSize(10)
    ->setFontName('Arial')
    ->setNumberFormat(new NumberFormat('MM/YYYY'))
    ->build();
```

**Comments**
```php
$worksheet = $writer->getCurrentSheet();
$worksheet->addComment(
    new Comment('A1', 'My comment', 'Comment user, null for nothing')
);
```

**Column Index from Cell Index**

Returns the column index (base 10) associated to the base 26 cell index.

Excel uses A to Z letters for column indexing, where A is the 1st column, Z is the 26th and AA is the 27th.

The mapping is zero based, so that 0 maps to A, B maps to 1, Z to 25 and AA to 26.
```php
// echo 0
echo CellHelper::getColumnToIndexFromCellIndex('A1');
```



Spout Full documentation can be found at [http://opensource.box.com/spout/](http://opensource.box.com/spout/).


## Requirements

* PHP version 5.6 or higher
* PHP extension `php_zip` enabled
* PHP extension `php_xmlreader` enabled

## Support

I will not offer full support for this fork, this is just a fork for a specific project.

## Copyright and License

Copyright 2022 Box, Inc. All rights reserved.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
