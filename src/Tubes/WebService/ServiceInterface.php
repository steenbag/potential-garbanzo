<?php namespace Steenbag\Tubes\WebService;

interface ServiceInterface
{

    /**
     * Return the name of this service.
     *
     * @return string
     */
    public static function getName();

    /**
     * Return the version of this service.
     *
     * @return mixed
     */
    public static function getVersion();

    /**
     * Return all of the methods that this service can respond to.
     *
     * @return array
     */
    public static function listProcedures();

    /**
     * Returns true if the requested procedure is available on this service.
     *
     * @param $procedure
     * @return boolean
     */
    public static function isProcedureCallable($procedure);

    /**
     * Return the name of the thrift service for this web service.
     *
     * @return string
     */
    public static function getThriftName();

    /**
     * Return the name of the Thrift processor class for this service.
     *
     * @return string
     */
    public static function getThriftProcessor();

    /**
     * Get the name of the Thrift client class for this service.
     *
     * @return string
     */
    public static function getThriftClient();

    /**
     * Get the name of the Rest middleware for this service.
     */
    public static function getThriftRest();

    /**
     * Returns the status of this service.
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Returns true if this service is available in the same application container as the current application.
     *
     * @return boolean
     */
    public function isLocal();

    /**
     * Returns true if this service is not available in the same application container as the current application.
     *
     * @return boolean
     */
    public function isRemote();

    /**
     * Get the configured endpoint.
     *
     * @return mixed
     */
    public function getEndpoint();

    /**
     * Set the endpoint.
     *
     * @param string $endpoint
     * @return mixed
     */
    public function setEndpoint($endpoint);

    /**
     * Return the configuration for this service.
     *
     * @return mixed
     */
    public function getConfig();

    /**
     * Initialize the configuration for this service.
     *
     * @param array $config
     * @return mixed
     */
    public function initConfig(array $config);

}
