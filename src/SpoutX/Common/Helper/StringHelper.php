<?php

declare(strict_types=1);

namespace SpoutX\Common\Helper;

/**
 * Class StringHelper
 * This class provides helper functions to work with strings and multibyte strings.
 *
 * @codeCoverageIgnore
 */
class StringHelper
{
    /** @var bool Whether the mbstring extension is loaded */
    protected static $hasMbstringSupport;

    /**
     *
     */
    public static function hasMbstringSupport()
    {
        if (self::$hasMbstringSupport === null) {
            self::$hasMbstringSupport = extension_loaded('mbstring');
        }
        return self::$hasMbstringSupport;
    }

    /**
     * Returns the length of the given string.
     * It uses the multi-bytes function is available.
     * @see strlen
     * @see mb_strlen
     *
     * @param string $string
     * @return int
     */
    public static function getStringLength($string)
    {
        return self::hasMbstringSupport() ? mb_strlen($string) : strlen($string);
    }

    /**
     * Returns the position of the first occurrence of the given character/substring within the given string.
     * It uses the multi-bytes function is available.
     * @see strpos
     * @see mb_strpos
     *
     * @param string $char Needle
     * @param string $string Haystack
     * @return int Char/substring's first occurrence position within the string if found (starts at 0) or -1 if not found
     */
    public static function getCharFirstOccurrencePosition($char, $string)
    {
        $position = self::hasMbstringSupport() ? mb_strpos($string, $char) : strpos($string, $char);

        return ($position !== false) ? $position : -1;
    }

    /**
     * Returns the position of the last occurrence of the given character/substring within the given string.
     * It uses the multi-bytes function is available.
     * @see strrpos
     * @see mb_strrpos
     *
     * @param string $char Needle
     * @param string $string Haystack
     * @return int Char/substring's last occurrence position within the string if found (starts at 0) or -1 if not found
     */
    public static function getCharLastOccurrencePosition($char, $string)
    {
        $position = self::hasMbstringSupport() ? mb_strrpos($string, $char) : strrpos($string, $char);

        return ($position !== false) ? $position : -1;
    }
}
