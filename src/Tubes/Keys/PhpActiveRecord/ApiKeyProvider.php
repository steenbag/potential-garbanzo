<?php namespace Steenbag\Tubes\Keys\PhpActiveRecord;

use Steenbag\Tubes\Contract;

class ApiKeyProvider implements Contract\ApiKeyProvider
{

    /**
     * Find the credential identified by an API Key and status.
     *
     * @param $apiKey
     * @return \Steenbag\Tubes\Keys\ApiKey
     */
    public function findActiveCredentialByApiKey($apiKey)
    {
        $params = [
            'active' => 1,
            'api_key' => $apiKey
        ];
        return ApiKey::first($params);
    }

    /**
     * Find the credential identified by an API Key.
     *
     * @param $apiKey
     * @return \Steenbag\Tubes\Keys\ApiKey
     */
    public function findCredentialByApiKey($apiKey)
    {
        $params = [
            'api_key' => $apiKey
        ];
        return ApiKey::first($params);
    }

    /**
     * Find the credential identified by an API Key, activation status and valid date range.
     *
     * @param $apiKey
     * @return \Steenbag\Tubes\Keys\ApiKey
     */
    public function findValidCredentialByApiKey($apiKey)
    {
        $now = new DateTime();
        return ApiKey::find_by_sql("select * from `api_keys` where `active` = ? and ((`valid_from` is null or `valid_from` <= ?) and (`valid_until` is null or `valid_until` >= ?)) and `api_key` = ? limit 1", [1, $now, $now, $apiKey]);
    }

    /**
     * Return the requested Api Key based on its slug identifier and status.
     *
     * @param $slug
     * @return \Steenbag\Tubes\Keys\ApiKey
     */
    public function findActiveCredentialBySlug($slug)
    {
        $params = [
            'active' => 1,
            'slug' => $slug
        ];
        return ApiKey::first($params);
    }

    /**
     * Return the requested Api Key based on its slug identifier.
     *
     * @param $slug
     * @return \Steenbag\Tubes\Keys\ApiKey
     */
    public function findCredentialBySlug($slug)
    {
        $params = [
            'slug' => $slug
        ];
        return ApiKey::first($params);
    }

    /**
     * Return the requested Api Key based on its slug identifier and valid status.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findValidCredentialBySlug($slug)
    {
        $now = new DateTime();
        return ApiKey::find_by_sql("select * from `api_keys` where `active` = ? and ((`valid_from` is null or `valid_from` <= ?) and (`valid_until` is null or `valid_until` >= ?)) and `slug` = ? limit 1", [1, $now, $now, $slug]);
    }

    /**
     * Delete the given API Key.
     *
     * @param Contract\ApiKey $key
     * @return bool
     */
    public function delete(Contract\ApiKey $key)
    {
        $key->delete();
    }

    /**
     * Return a new Query Builder from your ORM of choice.
     *
     * @return mixed
     */
    public function newQuery()
    {
        // TODO: Implement newQuery() method.
    }

    /**
     * Find an API Key by primary key.
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * Create a new ApiKey.
     *
     * @param array $attrs
     * @return ApiKey
     */
    public function create(array $attrs)
    {
        // TODO: Implement create() method.
    }

    /**
     * Update an ApiKey.
     *
     * @param $id
     * @param array $attrs
     * @return ApiKey
     */
    public function update($id, array $attrs)
    {
        // TODO: Implement update() method.
    }
}
