<?php

declare(strict_types=1);

namespace SpoutX\Common\Creator;

use SpoutX\Common\Helper\EncodingHelper;
use SpoutX\Common\Helper\FileSystemHelper;
use SpoutX\Common\Helper\GlobalFunctionsHelper;

/**
 * Class HelperFactory
 * Factory to create helpers
 */
class HelperFactory
{
    public function createGlobalFunctionsHelper(): GlobalFunctionsHelper
    {
        return new GlobalFunctionsHelper();
    }

    /**
     * @param string $baseFolderPath The path of the base folder where all the I/O can occur
     */
    public function createFileSystemHelper(string $baseFolderPath): FileSystemHelper
    {
        return new FileSystemHelper($baseFolderPath);
    }

    public function createEncodingHelper(GlobalFunctionsHelper $globalFunctionsHelper): EncodingHelper
    {
        return new EncodingHelper($globalFunctionsHelper);
    }
}
