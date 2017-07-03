<?php
/**
 * Created by PhpStorm.
 * User: fabianwidmann
 * Date: 20.06.17
 * Time: 10:10
 */

namespace FWidm\DWDHourlyCrawler\Exceptions;


class DWDLibException extends \Exception
{

    /**
     * DWDLibException constructor.
     * @param string $string
     */
    public function __construct($string)
    {
        $this->message=$string;
    }
}