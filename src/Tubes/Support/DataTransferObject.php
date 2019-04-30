<?php namespace Steenbag\Tubes\Support;

trait DataTransferObject
{

    /**
     * Create a new instance of the class.
     *
     * @param null $input
     * @return static
     */
    public static function make($input = null)
    {
        return new static($input);
    }

    /**
     * Get the value of the given member.
     *
     * @param $attribute
     * @return mixed
     */
    public function get($attribute) {
        if (property_exists($this, StringHelper::camelCase($attribute))) {
            $attribute = StringHelper::camelCase($attribute);
        } elseif (property_exists($this, StringHelper::snake_case($attribute))) {
            $attribute = StringHelper::snake_case($attribute);
        }

        return $this->$attribute;
    }

    public function __get($attribute)
    {
        return $this->get($attribute);
    }

    /**
     * Add support for dynamic getters and setters.
     *
     * @param $method
     * @param array $args
     * @return mixed|null
     */
    public function __call($method, array $args = [])
    {
        if (strpos($method, 'get') === 0) {
            $baseKey = substr($method, 3);
            if (property_exists($this, StringHelper::camelCase($baseKey))) {
                $baseKey = StringHelper::camelCase($baseKey);
            } else {
                return null;
            }
            return $this->$baseKey;
        }

        throw new \BadMethodCallException("Method {$method} is not defined on this class.");
    }

}
