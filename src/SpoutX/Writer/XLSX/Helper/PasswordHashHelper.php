<?php

declare(strict_types=1);

namespace SpoutX\Writer\XLSX\Helper;

/**
 * Computes the legacy 16-bit Excel password verifier used by the
 * <sheetProtection password="..."/> and <workbookProtection workbookPassword="..."/>
 * attributes. This is the same weak hash Excel itself writes for the legacy
 * password form; it deters casual edits, it is not cryptographic security.
 */
final class PasswordHashHelper
{
    public static function make(string $password): string
    {
        $verifier = 0;
        $pwlen = strlen($password);
        $passwordArray = pack('c', $pwlen) . $password;

        for ($i = $pwlen; $i >= 0; --$i) {
            $intermediate1 = (($verifier & 0x4000) === 0) ? 0 : 1;
            $intermediate2 = 2 * $verifier;
            $intermediate2 &= 0x7FFF;
            $intermediate3 = $intermediate1 | $intermediate2;
            $verifier = $intermediate3 ^ ord($passwordArray[$i]);
        }

        $verifier ^= 0xCE4B;

        return strtoupper(dechex($verifier));
    }
}
