<?php namespace Steenbag\Tubes\Factory;

use Steenbag\Tubes\Exception\UnsupportedClientException;

abstract class ThriftClientFactory implements \Steenbag\Tubes\Contract\ThriftClientFactory
{

    protected static $types = [];

    /**
     * Register a new type into the container.
     *
     * @param $type
     * @param $def
     */
    public static function registerType($type, $def)
    {
        static::$types[$type] = $def;
    }

    /**
     * Retrieve all of the types.
     *
     * @return array
     */
    public static function getTypes()
    {
        return static::$types;
    }

    /**
     * Get a specific type.
     *
     * @param $type
     * @return mixed
     * @throws UnsupportedClientException
     */
    public static function getType($type)
    {
        if (! static::isSupportedType($type)) {
            throw new UnsupportedClientException("'$type' is not a valid client type.");
        }

        return static::$types[$type];
    }

    /**
     * Return true if the requested type is supported.
     *
     * @param string$type
     * @return bool
     */
    public static function isSupportedType($type)
    {
        return array_key_exists($type, static::getTypes());
    }

}
