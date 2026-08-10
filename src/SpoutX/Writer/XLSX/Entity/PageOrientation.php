<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * Page orientation for printing.
 */
enum PageOrientation: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';
}
