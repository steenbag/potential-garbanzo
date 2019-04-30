<?php namespace Steenbag\Tubes\Descriptor;

class ExceptionDescription
{

    protected $exception;

    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }

}
