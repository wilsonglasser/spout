<?php

namespace SpoutX\Common\Entity\Style;

/**
 * Class NumberFormat
 * This class provides constants and functions to work with Number Formats
 */
class NumberFormat
{
    protected $formatCode;
    protected $id;

    public function __construct($formatCode = '')
    {
        $this->setFormatCode($formatCode);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getFormatCode()
    {
        return $this->formatCode;
    }

    public function setFormatCode($formatCode)
    {
        $this->formatCode = str_replace('"', '&quot;', $formatCode);
        return $this;
    }
}
