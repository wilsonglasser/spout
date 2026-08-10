<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * Print header/footer definitions. The strings use Excel's header/footer
 * formatting codes (e.g. "&L", "&C", "&R", "&P", "&N", "&D").
 */
class HeaderFooter
{
    public function __construct(
        public ?string $oddHeader = null,
        public ?string $oddFooter = null,
        public ?string $evenHeader = null,
        public ?string $evenFooter = null,
        public bool $differentOddEven = false,
    ) {
    }
}
