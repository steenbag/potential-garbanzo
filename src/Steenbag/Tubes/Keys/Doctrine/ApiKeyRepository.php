<?php namespace Steenbag\Tubes\Keys\Doctrine;

use Steenbag\Tubes\Contract\ApiKeyProvider;
use Steenbag\Tubes\Contract\ApiKey as ApiKeyContract;
use Doctrine\ORM\EntityRepository;

class ApiKeyRepository extends EntityRepository implements ApiKeyProvider
{

    /**
     * Find the credential identified by an API Key and status.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function findActiveCredentialByApiKey($apiKey)
    {
        $query = em()->createQuery('SELECT k FROM Steenbag\Tubes\Keys\Doctrine\ApiKey k WHERE k.active = true AND k.api_key = ?1')
            ->setParameter(1, $apiKey);

        $result = $query->getResult();

        return count($result) ? $result[0] : null;
    }

    /**
     * Find the credential identified by an API Key.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function findCredentialByApiKey($apiKey)
    {
        $query = em()->createQuery('SELECT k FROM Steenbag\Tubes\Keys\Doctrine\ApiKey k WHERE k.api_key = ?1')
            ->setParameter(1, $apiKey);

        $result = $query->getResult();

        return count($result) ? $result[0] : null;
    }

    /**
     * Find the credential identified by an API Key, activation status and valid date range.
     *
     * @param $apiKey
     * @return ApiKey
     * @throws \Exception
     */
    public function findValidCredentialByApiKey($apiKey)
    {
        $query = em()->createQuery('SELECT k FROM Steenbag\Tubes\Keys\Doctrine\ApiKey k WHERE k.active = true AND (k.valid_from IS NULL OR k.valid_from <= ?) AND (k.valid_until IS NULL OR k.valid_until >= ?) AND k.api_key = ?')
            ->setParameters([
                new \DateTime(),
                new \DateTime(),
                $apiKey
            ]);

        $result = $query->getResult();

        return count($result) ? $result[0] : null;
    }

    /**
     * Return the requested Api Key based on its slug identifier and status.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findActiveCredentialBySlug($slug)
    {
        $query = em()->createQuery('SELECT k FROM Steenbag\Tubes\Keys\Doctrine\ApiKey k WHERE k.active = true AND k.slug = ?1')
            ->setParameter(1, $slug);

        $result = $query->getResult();

        return count($result) ? $result[0] : null;
    }

    /**
     * Return the requested Api Key based on its slug identifier.
     *
     * @param $slug
     * @return ApiKey
     */
    public function findCredentialBySlug($slug)
    {
        $query = em()->createQuery('SELECT k FROM Steenbag\Tubes\Keys\Doctrine\ApiKey k WHERE k.slug = ?1')
            ->setParameter(1, $slug);

        $result = $query->getResult();

        return count($result) ? $result[0] : null;
    }

    /**
     * Return the requested Api Key based on its slug identifier and valid status.
     *
     * @param $slug
     * @return ApiKey
     * @throws \Exception
     */
    public function findValidCredentialBySlug($slug)
    {
        $query = em()->createQuery('SELECT k FROM Steenbag\Tubes\Keys\Doctrine\ApiKey k WHERE k.active = true AND (k.valid_from IS NULL OR k.valid_from <= ?) AND (k.valid_until IS NULL OR k.valid_until >= ?) AND k.slug = ?')
            ->setParameters([
                new \DateTime(),
                new \DateTime(),
                $slug
            ]);

        $result = $query->getResult();

        return count($result) ? $result[0] : null;
    }

    /**
     * Delete the given API Key.
     *
     * @param ApiKeyContract $key
     * @return bool
     */
    public function delete(ApiKeyContract $key)
    {
        // TODO: Implement delete() method.
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
        return parent::find($id);
    }

    /**
     * Create a new ApiKey.
     *
     * @param array $attrs
     * @return ApiKey
     */
    public function create(array $attrs)
    {
        $key = new \Steenbag\Tubes\Keys\Doctrine\ApiKey();
        $key->setApiKey($attrs['api_key']);
        $key->setPassword($attrs['password']);
        $key->setType($attrs['type']);
        $key->setActive($attrs['active']);
        $key->setClientName($attrs['client_name']);
        $key->setNotes($attrs['notes']);
        $key->setSlug($attrs['slug']);
        $key->setValidFrom($attrs['valid_from']);
        $key->getValidUntil($attrs['valid_until']);

        $this->_em->persist($key);
        $this->_em->flush();

        return $key;
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
        /** @var \Steenbag\Tubes\Keys\Doctrine\ApiKey $key */
        $key = $this->find($id);
        $key->setApiKey($attrs['api_key']);
        $key->setPassword($attrs['password']);
        $key->setType($attrs['type']);
        $key->setActive($attrs['active']);
        $key->setClientName($attrs['client_name']);
        $key->setNotes($attrs['notes']);
        $key->setSlug($attrs['slug']);
        $key->setValidFrom($attrs['valid_from']);
        $key->getValidUntil($attrs['valid_until']);

        $this->_em->persist($key);
        $this->_em->flush();

        return $key;
    }
}
