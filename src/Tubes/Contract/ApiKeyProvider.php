<?php namespace Steenbag\Tubes\Contract;

interface ApiKeyProvider
{

    /**
     * Find the credential identified by an API Key and status.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function findActiveCredentialByApiKey($apiKey);

    /**
     * Find the credential identified by an API Key.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function findCredentialByApiKey($apiKey);

    /**
     * Find the credential identified by an API Key, activation status and valid date range.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function findValidCredentialByApiKey($apiKey);

    /**
     * Return the requested Api Key based on its slug identifier and status.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findActiveCredentialBySlug($slug);

    /**
     * Return the requested Api Key based on its slug identifier.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findCredentialBySlug($slug);

    /**
     * Return the requested Api Key based on its slug identifier and valid status.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findValidCredentialBySlug($slug);

    /**
     * Delete the given API Key.
     *
     * @param ApiKey $key
     * @return bool
     */
    public function delete(ApiKey $key);

    /**
     * Return a new Query Builder from your ORM of choice.
     *
     * @return mixed
     */
    public function newQuery();

    /**
     * Find an API Key by primary key.
     *
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Create a new ApiKey.
     *
     * @param array $attrs
     * @return ApiKey
     */
    public function create(array $attrs);

    /**
     * Update an ApiKey.
     *
     * @param $id
     * @param array $attrs
     * @return ApiKey
     */
    public function update($id, array $attrs);

}
