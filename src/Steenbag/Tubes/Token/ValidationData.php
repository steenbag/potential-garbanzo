<?php namespace Steenbag\Tubes\Token;

use Steenbag\Tubes\Token\Claim\Claim;

class ValidationData
{

    protected $items;

    public function __construct($currentTime = null)
    {
        $currentTime = $currentTime ?: time();

        $this->items = [
            Claim::ISSUED_AT => $currentTime,
            Claim::NOT_BEFORE => $currentTime,
            Claim::EXPIRES => $currentTime,
            Claim::ISSUER => null,
            Claim::AUDIENCE => null,
            Claim::SUBJECT => null,
            Claim::ID => null,
            Claim::KEY_ID => null,
            Claim::USER_ID => null
        ];
    }

    /**
     * Set the ID.
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->items[Claim::ID] = $id;
    }

    /**
     * Set the Key ID.
     *
     * @param $id
     */
    public function setKeyId($id)
    {
        $this->items[Claim::KEY_ID] = $id;
    }

    /**
     * Set the User ID.
     *
     * @param $userId
     */
    public function setUserId($userId)
    {
        $this->items[Claim::USER_ID] = $userId;
    }

    /**
     * Set the issuer.
     *
     * @param string $issuer
     */
    public function setIssuer($issuer)
    {
        if (!is_array($issuer)) {
            $issuer = [$issuer];
        }
        $this->items[Claim::ISSUER] = array_values(array_map('strval', $issuer));
    }

    /**
     * Set the audience.
     *
     * @param string $audience
     */
    public function setAudience($audience)
    {
        $this->items[Claim::AUDIENCE] = $audience;
    }

    /**
     * Set the subject.
     *
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->items[Claim::SUBJECT] = $subject;
    }

    /**
     * Set the current time.
     *
     * @param int $currentTime
     */
    public function setCurrentTime($currentTime)
    {
        $this->items[Claim::ISSUED_AT] = $currentTime;
        $this->items[Claim::NOT_BEFORE] = $currentTime;
        $this->items[Claim::EXPIRES] = $currentTime;
    }

    /**
     * Set an arbitrary value.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->items[$name] = $value;
    }

    /**
     * Get the requested value.
     *
     * @param string $name
     * @return mixed|null
     */
    public function get($name)
    {
        return isset($this->items[$name]) ? $this->items[$name] : null;
    }

    /**
     * Returns if the requested value is set.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return !empty($this->items[$name]);
    }

}
