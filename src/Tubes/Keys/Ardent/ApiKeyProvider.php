<?php namespace Steenbag\Tubes\Keys\Ardent;

use Steenbag\Tubes\Contract;

class ApiKeyProvider implements Contract\ApiKeyProvider
{

    protected $model = 'Steenbag\Tubes\Keys\Ardent\ApiKey';

    public function createModel()
    {
        $model = $this->model;

        return new $model;
    }

    /**
     * Find the credential identified by an API Key and status.
     *
     * @param $apiKey
     * @return mixed
     */
    public function findActiveCredentialByApiKey($apiKey)
    {
        return $this->newQuery()->active()->where('api_key', $apiKey)->first();
    }

    /**
     * Find the credential identified by an API Key.
     *
     * @param $apiKey
     * @return mixed
     */
    public function findCredentialByApiKey($apiKey)
    {
        return $this->newQuery()->where('api_key', $apiKey)->first();
    }

    /**
     * Return the requested Api Key based on its slug identifier.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findActiveCredentialBySlug($slug)
    {
        return $this->newQuery()->active()->where('slug', $slug)->first();
    }

    /**
     * Return the requested Api Key based on its slug identifier.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findCredentialBySlug($slug)
    {
        return $this->newQuery()->where('slug', $slug)->first();
    }

    /**
     * Find the credential identified by an API Key, activation status and valid date range.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function findValidCredentialByApiKey($apiKey)
    {
        return $this->newQuery()->valid()->where('api_key', $apiKey)->first();
    }

    /**
     * Return the requested Api Key based on its slug identifier and valid status.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findValidCredentialBySlug($slug)
    {
        return $this->newQuery()->valid()->where('slug', $slug)->first();
    }

    /**
     * Delete the given API Key.
     *
     * @param Contract\ApiKey $key
     * @return bool
     */
    public function delete(Contract\ApiKey $key)
    {
        return $key->delete();
    }

    /**
     * Return a new Query Builder from your ORM of choice.
     *
     * @return mixed
     */
    public function newQuery()
    {
        $instance = $this->createModel();

        return $instance->newQuery();
    }

    /**
     * Find an API Key by primary key.
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->createModel()->find($id);
    }

    /**
     * Create a new ApiKey.
     *
     * @param array $attrs
     * @return ApiKey
     */
    public function create(array $attrs)
    {
        $model = $this->model;

        return $model::create($attrs);
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
        $instance = $this->find($id);

        return $instance->update($attrs);
    }
}
