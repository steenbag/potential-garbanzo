<?php namespace Steenbag\Tubes\Contract;

interface ApiKey
{

    /**
     * Return the password for the key.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Returns true if the API Key is active.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Returns true if the API Key is valid.
     *
     * @return boolean
     */
    public function isValid();

    /**
     * Return all of the valid grants for this API Key.
     *
     * @return array
     */
    public function getGrants();

    /**
     * Return all of the valid referrers for this API Key.
     *
     * @return array
     */
    public function getValidReferrers();

    /**
     * Returns true if the passed-in grants is valid.
     *
     * @param string $api
     * @param string $method
     * @return bool
     */
    public function isValidGrant($api, $method);

    /**
     * Delete the Key.
     *
     * @return bool
     */
    public function delete();

}
