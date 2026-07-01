<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * How Excel reacts to an invalid entry (the "errorStyle" attribute).
 */
enum ValidationErrorStyle: string
{
    case Stop = 'stop';
    case Warning = 'warning';
    case Information = 'information';
}
