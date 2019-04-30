<?php namespace Steenbag\Tubes\Contract;

interface ThriftClientFactory
{

    /**
     * Create a new client instance.
     *
     * @param $type
     * @param $endpoint
     * @param $protocol
     * @return mixed
     */
    public function createClient($type, $endpoint, $protocol);

    /**
     * Return true if the requested type is supported.
     *
     * @param string$type
     * @return bool
     * @static
     */
    public static function isSupportedType($type);

    /**
     * Return the requested Thrift Protocol.
     *
     * @param $protocol
     * @param $buffer
     * @return TProtocol
     */
    public function getThriftProtocol($protocol, $buffer);

}
