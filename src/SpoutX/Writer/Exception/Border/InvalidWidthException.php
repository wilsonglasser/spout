<?php

namespace SpoutX\Writer\Exception\Border;

use SpoutX\Common\Entity\Style\BorderPart;
use SpoutX\Writer\Exception\WriterException;

class InvalidWidthException extends WriterException
{
    public function __construct($name)
    {
        $msg = '%s is not a valid width identifier for a border. Valid identifiers are: %s.';

        parent::__construct(sprintf($msg, $name, implode(',', BorderPart::getAllowedWidths())));
    }
}
