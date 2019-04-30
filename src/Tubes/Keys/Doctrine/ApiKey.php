<?php namespace Steenbag\Tubes\Keys\Doctrine;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="Steenbag\Tubes\Keys\Doctrine\ApiKeyRepository")
 * @ExclusionPolicy("all")
 */
class ApiKey implements \Steenbag\Tubes\Contract\ApiKey
{

    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="api_key_pk_seq", initialValue=1)
     * @Expose
     * @var integer $id
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $client_name
     */
    protected $client_name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Expose
     * @var string $notes
     */
    protected $notes;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $slug
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $type
     */
    protected $type;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $api_key
     */
    protected $api_key;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $password
     */
    protected $password;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Expose
     * @var string $active
     */
    protected $active;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Expose
     * @var string $valid_from
     */
    protected $valid_from;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Expose
     * @var string $valid_until
     */
    protected $valid_until;

    /**
     * @ORM\OneToMany(targetEntity="Steenbag\Tubes\Keys\Doctrine\ApiGrant", mappedBy="apiKey", cascade={"persist", "remove"})
     * @Expose
     */
    protected $grants;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ApiKey
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientName()
    {
        return $this->client_name;
    }

    /**
     * @param string $client_name
     * @return ApiKey
     */
    public function setClientName($client_name)
    {
        $this->client_name = $client_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return ApiKey
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return ApiKey
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ApiKey
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param string $api_key
     * @return ApiKey
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return ApiKey
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Returns true if the API Key is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getActive() === true;
    }

    /**
     * @param string $active
     * @return ApiKey
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidFrom()
    {
        return $this->valid_from;
    }

    /**
     * @param string $valid_from
     * @return ApiKey
     */
    public function setValidFrom($valid_from)
    {
        $this->valid_from = $valid_from;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidUntil()
    {
        return $this->valid_until;
    }

    /**
     * @param string $valid_until
     * @return ApiKey
     */
    public function setValidUntil($valid_until)
    {
        $this->valid_until = $valid_until;
        return $this;
    }

    /**
     * Add a magical property accessor.
     *
     * @param $prop
     * @return mixed
     */
    public function __get($prop)
    {
        $method = "get" . ucfirst(camel_case($prop));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        return isset($this->{$prop}) ? $this->{$prop} : null;
    }



    /**
     * Returns true if the API Key is valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        if (! $this->isActive()) {
            return false;
        }

        $now = \Carbon::now();

        if (isset($this->valid_from) && $now->lt($this->valid_from)) {
            return false;
        }

        if (isset($this->valid_until) && $now->gt($this->valid_until)) {
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
     * Delete the Key.
     *
     * @return bool
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }
}
