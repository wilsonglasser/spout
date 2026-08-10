<?php

declare(strict_types=1);

namespace SpoutX\Common\Helper;

/**
 * Class GlobalFunctionsHelper
 * This class wraps global functions to facilitate testing
 *
 * @codeCoverageIgnore
 */
class GlobalFunctionsHelper
{
    /**
     * Wrapper around global function fopen()
     * @see fopen()
     *
     * @return resource|bool
     */
    public function fopen(string $fileName, string $mode)
    {
        return fopen($fileName, $mode);
    }

    /**
     * Wrapper around global function fgets()
     * @see fgets()
     *
     * @param resource $handle
     * @return string
     */
    public function fgets($handle, ?int $length = null)
    {
        return fgets($handle, $length);
    }

    /**
     * Wrapper around global function fputs()
     * @see fputs()
     *
     * @param resource $handle
     * @return int
     */
    public function fputs($handle, string $string)
    {
        return fputs($handle, $string);
    }

    /**
     * Wrapper around global function fflush()
     * @see fflush()
     *
     * @param resource $handle
     */
    public function fflush($handle): bool
    {
        return fflush($handle);
    }

    /**
     * Wrapper around global function fseek()
     * @see fseek()
     *
     * @param resource $handle
     */
    public function fseek($handle, int $offset): int
    {
        return fseek($handle, $offset);
    }

    /**
     * Wrapper around global function fwrite()
     * @see fwrite()
     *
     * @param resource $handle
     * @return int
     */
    public function fwrite($handle, string $string)
    {
        return fwrite($handle, $string);
    }

    /**
     * Wrapper around global function fclose()
     * @see fclose()
     *
     * @param resource $handle
     */
    public function fclose($handle): bool
    {
        return fclose($handle);
    }

    /**
     * Wrapper around global function rewind()
     * @see rewind()
     *
     * @param resource $handle
     */
    public function rewind($handle): bool
    {
        return rewind($handle);
    }

    /**
     * Wrapper around global function file_exists()
     * @see file_exists()
     */
    public function file_exists(string $fileName): bool
    {
        return file_exists($fileName);
    }

    /**
     * Wrapper around global function file_get_contents()
     * @see file_get_contents()
     *
     * @return string
     */
    public function file_get_contents(string $filePath)
    {
        $realFilePath = $this->convertToUseRealPath($filePath);

        return file_get_contents($realFilePath);
    }

    /**
     * Updates the given file path to use a real path.
     * This is to avoid issues on some Windows setup.
     *
     * @param string $filePath File path
     * @return string The file path using a real path
     */
    protected function convertToUseRealPath(string $filePath)
    {
        $realFilePath = $filePath;

        if ($this->isZipStream($filePath)) {
            if (preg_match('/zip:\/\/(.*)#(.*)/', $filePath, $matches)) {
                $documentPath = $matches[1];
                $documentInsideZipPath = $matches[2];
                $realFilePath = 'zip://' . realpath($documentPath) . '#' . $documentInsideZipPath;
            }
        } else {
            $realFilePath = realpath($filePath);
        }

        return $realFilePath;
    }

    /**
     * Returns whether the given path is a zip stream.
     *
     * @param string $path Path pointing to a document
     * @return bool TRUE if path is a zip stream, FALSE otherwise
     */
    protected function isZipStream(string $path): bool
    {
        return (strpos($path, 'zip://') === 0);
    }

    /**
     * Wrapper around global function feof()
     * @see feof()
     *
     * @param resource $handle
     */
    public function feof($handle): bool
    {
        return feof($handle);
    }

    /**
     * Wrapper around global function is_readable()
     * @see is_readable()
     */
    public function is_readable(string $fileName): bool
    {
        return is_readable($fileName);
    }

    /**
     * Wrapper around global function basename()
     * @see basename()
     */
    public function basename(string $path, string $suffix = ''): string
    {
        return basename($path, $suffix);
    }

    /**
     * Wrapper around global function header()
     * @see header()
     */
    public function header(string $string): void
    {
        header($string);
    }

    /**
     * Wrapper around global function ob_end_clean()
     * @see ob_end_clean()
     */
    public function ob_end_clean(): void
    {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Wrapper around global function iconv()
     * @see iconv()
     *
     * @param string $string The string to be converted
     * @param string $sourceEncoding The encoding of the source string
     * @param string $targetEncoding The encoding the source string should be converted to
     * @return string|bool the converted string or FALSE on failure.
     */
    public function iconv(string $string, string $sourceEncoding, string $targetEncoding)
    {
        return iconv($sourceEncoding, $targetEncoding, $string);
    }

    /**
     * Wrapper around global function mb_convert_encoding()
     * @see mb_convert_encoding()
     *
     * @param string $string The string to be converted
     * @param string $sourceEncoding The encoding of the source string
     * @param string $targetEncoding The encoding the source string should be converted to
     * @return string|bool the converted string or FALSE on failure.
     */
    public function mb_convert_encoding(string $string, string $sourceEncoding, string $targetEncoding)
    {
        return mb_convert_encoding($string, $targetEncoding, $sourceEncoding);
    }

    /**
     * Wrapper around global function stream_get_wrappers()
     * @see stream_get_wrappers()
     *
     * @return array
     */
    public function stream_get_wrappers(): array
    {
        return stream_get_wrappers();
    }

    /**
     * Wrapper around global function function_exists()
     * @see function_exists()
     */
    public function function_exists(string $functionName): bool
    {
        return function_exists($functionName);
    }
}
