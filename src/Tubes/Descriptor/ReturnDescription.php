<?php namespace Steenbag\Tubes\Descriptor;

class ReturnDescription
{

    protected $type;

    protected $description;

    public function __construct($type, $description)
    {
        $this->type = $type;
        $this->description = $description;
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

}
