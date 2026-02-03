<?php

namespace WilsonGlasser\Spout\Writer\ODS\Creator;

use WilsonGlasser\Spout\Common\Helper\Escaper;
use WilsonGlasser\Spout\Common\Manager\OptionsManagerInterface;
use WilsonGlasser\Spout\Writer\Common\Creator\InternalEntityFactory;
use WilsonGlasser\Spout\Writer\Common\Entity\Options;
use WilsonGlasser\Spout\Writer\Common\Helper\ZipHelper;
use WilsonGlasser\Spout\Writer\ODS\Helper\FileSystemHelper;

/**
 * Class HelperFactory
 * Factory for helpers needed by the ODS Writer
 */
class HelperFactory extends \WilsonGlasser\Spout\Common\Creator\HelperFactory
{
    /**
     * @param OptionsManagerInterface $optionsManager
     * @param InternalEntityFactory $entityFactory
     * @return FileSystemHelper
     */
    public function createSpecificFileSystemHelper(OptionsManagerInterface $optionsManager, InternalEntityFactory $entityFactory)
    {
        $tempFolder = $optionsManager->getOption(Options::TEMP_FOLDER);
        $zipHelper = $this->createZipHelper($entityFactory);

        return new FileSystemHelper($tempFolder, $zipHelper);
    }

    /**
     * @param InternalEntityFactory $entityFactory
     * @return ZipHelper
     */
    private function createZipHelper($entityFactory)
    {
        return new ZipHelper($entityFactory);
    }

    /**
     * @return Escaper\ODS
     */
    public function createStringsEscaper()
    {
        return new Escaper\ODS();
    }

}
