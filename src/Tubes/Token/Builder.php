<?php namespace Steenbag\Tubes\Token;

use Steenbag\Tubes\Token\Claim\Claim;
use Steenbag\Tubes\Token\Claim\Factory;
use Steenbag\Tubes\Token\Claim\Validatable;
use Namshi\JOSE\JWS;

class Builder
{

    protected $headers;

    protected $claims;

    protected $claimFactory;

    protected $jws;

    protected $signature;

    public function __construct(Factory $factory = null)
    {
        $this->claimFactory = $factory ?: new Factory;
        $this->headers = ['typ' => 'JWT', 'alg' => 'RS256'];
        $this->claims = [];
        $this->jws = new JWS($this->headers, 'SecLib');
    }

    /**
     * Set the audience for the token.
     *
     * @param $audience
     * @param bool $replicateAsHeader
     * @return Builder
     */
    public function setAudience($audience, $replicateAsHeader = false)
    {
        if (!is_array($audience)) {
            $audience = [$audience];
        }

        return $this->registerClaim(Claim::AUDIENCE, array_values(array_map('strval', $audience)), $replicateAsHeader);
    }

    /**
     * Set the expiration time.
     *
     * @param $expiration
     * @param bool $replicateAsHeader
     * @return Builder
     */
    public function expiresAt($expiration, $replicateAsHeader = false)
    {
        return $this->registerClaim(Claim::EXPIRES, $expiration, $replicateAsHeader);
    }

    /**
     * Set the token ID.
     *
     * @param $id
     * @param $replicateAsHeader
     * @return Builder
     */
    public function setId($id, $replicateAsHeader = false)
    {
        return $this->registerClaim(Claim::ID, $id, $replicateAsHeader);
    }

    /**
     * Provide a key identifier.
     *
     * @param $id
     * @param bool $replicateAsHeader
     * @return Builder
     */
    public function setKeyId($id, $replicateAsHeader = false)
    {
        return $this->registerClaim(Claim::KEY_ID, $id, $replicateAsHeader);
    }

    /**
     * Provide the User Id.
     *
     * @param $userId
     * @param bool $replicateAsHeader
     * @return Builder
     */
    public function setUserId($userId, $replicateAsHeader = false)
    {
        return $this->registerClaim(Claim::USER_ID, $userId, $replicateAsHeader);
    }

    /**
     * Set the issued at time.
     *
     * @param $issuedAt
     * @param bool $replicateAsHeader
     * @return Builder
     */
    public function issuedAt($issuedAt, $replicateAsHeader = false)
    {
        return $this->registerClaim(Claim::ISSUED_AT, $issuedAt, $replicateAsHeader);
    }

    /**
     * Set the issuer.
     *
     * @param $issuer
     * @param bool $replicateAsHeder
     * @return Builder
     */
    public function issuedBy($issuer, $replicateAsHeder = false)
    {
        return $this->registerClaim(Claim::ISSUER, $issuer, $replicateAsHeder);
    }

    /**
     * Set the time before which the token cannot be accepted.
     *
     * @param $notBefore
     * @param bool $replicateAsHeader
     * @return Builder
     */
    public function notValidBefore($notBefore, $replicateAsHeader = false)
    {
        return $this->registerClaim(Claim::NOT_BEFORE , $notBefore, $replicateAsHeader);
    }

    /**
     * Set the token subject.
     *
     * @param $subject
     * @param $replicateAsHeader
     * @return Builder
     */
    public function setSubject($subject, $replicateAsHeader)
    {
        return $this->registerClaim(Claim::SUBJECT, $subject, $replicateAsHeader);
    }

    /**
     * Add a new claim to the collection.
     *
     * @param $name
     * @param $value
     * @param $replicate
     * @return $this
     */
    protected function registerClaim($name, $value, $replicate)
    {
        $this->set($name, $value);

        if ($replicate) {
            $this->headers[$name] = $this->claims[$name];
        }

        return $this;
    }

    /**
     * Set a claim.
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->ensureUnsigned();

        $this->claims[$name] = $this->claimFactory->create($name, $value);

        return $this;
    }

    /**
     * Set a header.
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->ensureUnsigned();

        $this->headers[$name] = $this->claimFactory->create($name, $value);

        return $this;
    }

    /**
     * Return the signed value.
     *
     * @param $key
     * @param string $password
     * @return $this
     */
    public function sign($key, $password)
    {
        if (!isset($this->claims[Claim::ISSUED_AT])) {
            $this->issuedAt(with(new \DateTime('now'))->format('U'));
        }
        $this->jws->setPayload($this->claims);
        $this->jws->setHeader($this->headers);
        $this->signature = $this->jws->sign($key, $password);

        return $this;
    }

    /**
     * Returns whether or not we have signed the token.
     *
     * @return bool
     */
    public function isSigned()
    {
        return $this->jws->isSigned();
    }

    /**
     * Re-initialize the JWS.
     *
     * @return $this
     */
    public function unsign()
    {
        $this->jws = new JWS(['alg' => 'RS256'], 'SecLib');

        $this->jws->setPayload($this->claims);
        $this->jws->setHeader($this->headers);

        return $this;
    }

    /**
     * Return our token string.
     *
     * @return string
     */
    public function getTokenString()
    {
        return $this->jws->getTokenString();
    }

    /**
     * Load a token string so that we may verify it.
     *
     * @param $tokenString
     * @return static
     */
    public static function load($tokenString, $factory = null)
    {
        $instance = new static($factory);
        $instance->setJws(JWS::load($tokenString, false, null, 'SecLib'));

        return $instance;
    }

    /**
     * Verify the previously-loaded token.
     *
     * @param $key
     * @param ValidationData $data
     * @return bool
     */
    public function verify($key, ValidationData $data = null)
    {
        $signatureValid = $this->jws->verify($key, 'RS256');

        if ($signatureValid) {
            return $this->validateClaims($data);
        }

        return $signatureValid;
    }

    /**
     * Return the value of a given claim.
     *
     * @param $name
     * @return string
     */
    public function getClaim($name)
    {
        return empty($this->claims[$name]) ? null : $this->claims[$name];
    }

    /**
     * Validate our claims against the token.
     *
     * @param ValidationData $data
     * @return bool
     */
     public function validateClaims(ValidationData $data)
     {
        foreach ($this->getValidatableClaims() as $claim) {
            if (! $claim->validate($data)) {
                return false;
            }
        }

         return true;
     }

    /**
     * Return all of our claims that implement the Validatable interface.
     */
    protected function getValidatableClaims()
    {
        $claims = [];
        foreach ($this->claims as $claim) {
            if ($claim instanceof Validatable) {
                $claims []= $claim;
            }
        }

        return $claims;
    }

    /**
     * Throw an exception is our JWS is alreay signed.
     * This prevents us from making changes that are not incorporated into the signature.
     */
    protected function ensureUnsigned()
    {
        if ($this->isSigned()) {
            throw new \BadMethodCallException("You must unsign before making changes.");
        }
    }

    protected function setJws(JWS $jws)
    {
        $this->jws = $jws;
        foreach ($jws->getPayload() as $name => $value) {
            $this->set($name, $value);
        }
    }

}
