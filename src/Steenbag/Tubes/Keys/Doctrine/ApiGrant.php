<?php namespace Steenbag\Tubes\Keys\Doctrine;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class ApiGrant
{

    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="api_grant_pk_seq", initialValue=1)
     * @Expose
     * @var integer $id
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Steenbag\Tubes\Keys\Doctrine\ApiKey", inversedBy="grants")
     * @expose
     */
    protected $apiKey;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $api
     */
    protected $api;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Expose
     * @var string $method
     */
    protected $method;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Expose
     * @var string $value
     */
    protected $value;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ApiGrant
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     * @return ApiGrant
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param string $api
     * @return ApiGrant
     */
    public function setApi($api)
    {
        $this->api = $api;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return ApiGrant
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ApiGrant
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

}
