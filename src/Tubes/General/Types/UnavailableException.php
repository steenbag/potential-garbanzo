<?php
namespace Steenbag\Tubes\General\Types;

/**
 * Autogenerated by Thrift Compiler (0.12.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;
use JsonSerializable;
use stdClass;

/**
 * One or more required services was unavailable.
 */
class UnavailableException extends TException implements JsonSerializable
{
    static public $isValidate = true;

    static public $_TSPEC = array(
    );


    public function __construct()
    {
    }

    public function getName()
    {
        return 'UnavailableException';
    }


    public function read($input)
    {
        return $this->_read('UnavailableException', self::$_TSPEC, $input);
    }


    public function write($output)
    {
        return $this->_write('UnavailableException', self::$_TSPEC, $output);
    }


    public function jsonSerialize() {
        $json = new stdClass;
        return $json;
    }
}
