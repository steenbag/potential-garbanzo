<?php
namespace Steenbag\Tubes\General\Auth;

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

final class AuthRejectionCode
{
    const BAD_KEY = 1;

    const EXPIRED = 2;

    const BAD_GRANT = 3;

    const INVALID_SIGNATURE = 4;

    const DISABLED_KEY = 5;

    const INVALID_LOGIN = 6;

    static public $__names = array(
        1 => 'BAD_KEY',
        2 => 'EXPIRED',
        3 => 'BAD_GRANT',
        4 => 'INVALID_SIGNATURE',
        5 => 'DISABLED_KEY',
        6 => 'INVALID_LOGIN',
    );
}

