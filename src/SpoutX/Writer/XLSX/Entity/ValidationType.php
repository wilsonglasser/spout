<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * Data-validation rule type (the "type" attribute of <dataValidation>).
 */
enum ValidationType: string
{
    case Whole = 'whole';
    case Decimal = 'decimal';
    case Date = 'date';
    case Time = 'time';
    case TextLength = 'textLength';
    case List = 'list';
    case Custom = 'custom';
}
