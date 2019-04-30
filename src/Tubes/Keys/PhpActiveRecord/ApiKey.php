<?php namespace Steenbag\Tubes\Keys\PhpActiveRecord;

class ApiKey extends \ActiveRecord\Model implements \Steenbag\Tubes\Contract\ApiKey
{

    /**
     * Returns true if the API Key is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->active;
    }

    /**
     * Returns true if the API Key is valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        if (! $this->active()) {
            return false;
        }

        $now = new \DateTime();

        if (isset($this->valid_from) && $now < $this->valid_from) {
            return false;
        }

        if (isset($this->valid_until) && $now > $this->valid_until) {
            return false;
        }

        return true;
    }

    /**
     * Return all of the valid grants for this API Key.
     *
     * @return array
     */
    public function getGrants()
    {
        // TODO: Implement getGrants() method.
    }

    /**
     * Return all of the valid referrers for this API Key.
     *
     * @return array
     */
    public function getValidReferrers()
    {
        // TODO: Implement getValidReferrers() method.
    }

    /**
     * Returns true if the passed-in grants is valid.
     *
     * @param string $api
     * @param string $method
     * @return bool
     */
    public function isValidGrant($api, $method)
    {
        // TODO: Implement isValidGrant() method.
    }

    /**
     * Return the password for the key.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Delete the Key.
     *
     * @return bool
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }
}
