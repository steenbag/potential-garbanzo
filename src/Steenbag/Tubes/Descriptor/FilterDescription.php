<?php namespace Steenbag\Tubes\Descriptor;

class FilterDescription
{

    protected $name;

    protected $type;

    protected $description;

    protected $default;

    public function __construct($name, $type, $description, $default = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->default = $default;
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
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

}
