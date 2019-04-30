<?php namespace Steenbag\Tubes\Token\Claim;

class Claim implements ClaimInterface
{

    const AUDIENCE = 'aud';

    const EXPIRES = 'exp';

    const ID = 'jti';

    const ISSUED_AT = 'iat';

    const ISSUER = 'iss';

    const NOT_BEFORE = 'nbf';

    const SUBJECT = 'sub';

    const KEY_ID = 'kid';

    const USER_ID = 'uid';

    protected $name;

    protected $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->value;
    }

}
