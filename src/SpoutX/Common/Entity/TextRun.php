<?php

declare(strict_types=1);

namespace SpoutX\Common\Entity;

/**
 * A single formatted run of text within a {@see RichText} cell value.
 * Unset font properties fall back to Excel's defaults.
 */
class TextRun
{
    public function __construct(
        public string $text,
        public ?int $fontSize = null,
        public ?string $fontColor = null,
        public ?string $fontName = null,
        public bool $bold = false,
        public bool $italic = false,
        public bool $underline = false,
        public bool $strikethrough = false,
    ) {
    }
}
