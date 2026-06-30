<?php

declare(strict_types=1);

namespace SpoutX\Autoloader;

require_once 'Psr4Autoloader.php';

/**
 * @var string
 * Full path to "src/SpoutX" which is what we want "SpoutX" to map to.
 */
$srcBaseDirectory = dirname(dirname(__FILE__));

$loader = new Psr4Autoloader();
$loader->register();
$loader->addNamespace('SpoutX', $srcBaseDirectory);
