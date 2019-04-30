<?php namespace Steenbag\Tubes\Token\Claim;

class Factory
{

    protected $callbacks;

    public function __construct (array $callbacks = [])
    {
        $this->callbacks = array_merge(
            [
                Claim::ISSUED_AT => [$this, 'createLessThanEquals'],
                Claim::NOT_BEFORE => [$this, 'createLessThanEquals'],
                Claim::EXPIRES => [$this, 'createGreaterThanEquals'],
                Claim::ISSUER => [$this, 'createContainedEqualsTo'],
                Claim::AUDIENCE => [$this, 'creatContainsEqualsTo'],
                Claim::SUBJECT => [$this, 'createEqualTo'],
                Claim::ID => [$this, 'createEqualTo'],
                Claim::KEY_ID => [$this, 'createEqualTo'],
                Claim::USER_ID => [$this, 'createEqualTo']
            ],
            $callbacks
        );
    }

    public function create($name, $value)
    {
        if (! empty($this->callbacks[$name])) {
            return call_user_func($this->callbacks[$name], $name, $value);
        }

        return $this->createBasic($name, $value);
    }

    /**
     * Create a basic claim.
     *
     * @param $name
     * @param $value
     * @return Claim
     */
    protected function createBasic($name, $value)
    {
        return new Claim($name, $value);
    }

    /**
     * Create a less than equals claim.
     *
     * @param $name
     * @param $value
     * @return LessThenEquals
     */
    protected function createLessThanEquals($name, $value)
    {
        return new LessThenEquals($name, $value);
    }

    /**
     * Create a greater than equals claim.
     *
     * @param $name
     * @param $value
     * @return GreaterThanEquals
     */
    protected function createGreaterThanEquals($name, $value)
    {
        return new GreaterThanEquals($name, $value);
    }

    /**
     * Create a contained in claim.
     *
     * @param $name
     * @param $value
     * @return ContainedEqualsTo
     */
    protected function createContainedEqualsTo($name, $value)
    {
        return new ContainedEqualsTo($name, $value);
    }

    /**
     * Create a contains claim.
     *
     * @param $name
     * @param $value
     * @return ContainsEqualsTo
     */
    protected function creatContainsEqualsTo($name, $value)
    {
        return new ContainsEqualsTo($name, $value);
    }

    /**
     * Create an equal to claim.
     *
     * @param $name
     * @param $value
     * @return EqualTo
     */
    protected function createEqualTo($name, $value)
    {
        return new EqualTo($name, $value);
    }

}
