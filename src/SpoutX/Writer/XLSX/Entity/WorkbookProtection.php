<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Entity;

use SpoutX\Writer\XLSX\Helper\PasswordHashHelper;

/**
 * Workbook-level protection (<workbookProtection>). lockStructure prevents users
 * from adding, deleting, renaming, moving and — notably — hiding/unhiding sheets,
 * so it is what keeps a Hidden sheet effectively hidden. Optionally guarded by a
 * password (legacy 16-bit verifier).
 */
class WorkbookProtection
{
    public function __construct(
        public ?string $password = null,
        public bool $lockStructure = false,
        public bool $lockWindows = false,
        public bool $lockRevisions = false,
    ) {
    }

    public function getXml(): string
    {
        $attributes = [
            'workbookPassword' => $this->password !== null ? PasswordHashHelper::make($this->password) : '',
            'lockStructure' => $this->lockStructure,
            'lockWindows' => $this->lockWindows,
            'lockRevisions' => $this->lockRevisions,
        ];

        $xml = '<workbookProtection';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $xml .= ' ' . $name . '="' . $value . '"';
        }

        return $xml . '/>';
    }
}
