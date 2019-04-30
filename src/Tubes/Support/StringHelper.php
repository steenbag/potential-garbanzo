<?php namespace Steenbag\Tubes\Support;

class StringHelper
{

    /**
     * Convert a string to snake_case.
     *
     * @param $str
     * @param string $delim
     * @return string
     */
    public static function snake_case($str, $delim = '_')
    {
        $replace = "$1{$delim}$2";

        return ctype_lower($str) ? $str : strtolower(preg_replace('/(.)([A-Z])/', $replace, $str));
    }

    /**
     * Convert a string to StudlyCase
     *
     * @param $str
     * @return mixed
     */
    public static function StudlyCase($str)
    {
        $str = ucwords(str_replace(array('-', '_'), ' ', $str));

        return str_replace(' ', '', $str);
    }

    /**
     * Convert a string to camelCase.
     *
     * @param $str
     * @return string
     */
    public static function camelCase($str)
    {
        return lcfirst(static::StudlyCase($str));
    }

}
