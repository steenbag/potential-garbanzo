<?php namespace Steenbag\Tubes\SharedSdk;

use DateTime;

trait DateParser
{

    protected $defaultDateFormat = DateTime::ATOM;

    protected function parseDate($date, $format = null)
    {
        return DateTime::createFromFormat($format ?: $this->defaultDateFormat, $date);
    }

}
