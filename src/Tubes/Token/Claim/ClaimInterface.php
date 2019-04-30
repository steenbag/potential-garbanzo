<?php namespace Steenbag\Tubes\Token\Claim;

interface ClaimInterface extends \JsonSerializable
{

    /**
     * Retur nthe claim name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return the claim value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Return the claim value as a string.
     *
     * @return mixed
     */
    public function __toString();

}
