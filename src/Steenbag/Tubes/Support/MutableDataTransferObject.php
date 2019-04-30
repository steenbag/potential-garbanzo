<?php namespace Steenbag\Tubes\Support;


trait MutableDataTransferObject
{

    use DataTransferObject;

    /**
     * Set the given member.
     *
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function set($attribute, $value) {
        if (property_exists($this, camelCase($attribute))) {
            $attribute = camelCase($attribute);
        } elseif (property_exists($this, snake_case($attribute))) {
            $attribute = snake_case($attribute);
        }

        return $this->$attribute = $value;
    }

    public function __set($attribute, $value)
    {
        return $this->set($attribute, $value);
    }

}
