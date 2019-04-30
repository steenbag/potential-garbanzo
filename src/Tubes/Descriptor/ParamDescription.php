<?php namespace Steenbag\Tubes\Descriptor;

class ParamDescription
{

    protected $name;

    protected $type;

    protected $description;

    protected $default;

    public function __construct($name, $type, $description, $reflection = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        if ($reflection) {
            if ($reflection->isDefaultValueAvailable()) {
                $this->default = $reflection->getDefaultValue();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return is_null($this->default);
    }

}
