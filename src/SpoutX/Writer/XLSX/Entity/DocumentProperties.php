<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

/**
 * Workbook document properties written to docProps/core.xml and docProps/app.xml
 * (title, author, keywords, ...). All fields are optional; only the ones set are
 * emitted. Custom properties are not supported.
 */
class DocumentProperties
{
    public function __construct(
        public ?string $title = null,
        public ?string $subject = null,
        public ?string $creator = null,
        public ?string $lastModifiedBy = null,
        public ?string $keywords = null,
        public ?string $description = null,
        public ?string $category = null,
        public ?string $language = null,
        public ?string $application = null,
    ) {
    }
}
