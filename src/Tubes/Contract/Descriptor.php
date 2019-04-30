<?php namespace Steenbag\Tubes\Contract;

use Steenbag\Tubes\WebService\ServiceInterface;

interface Descriptor
{

    /**
     * Describe the passed-in service.
     *
     * @param mixed $object
     * @param array $options
     * @return mixed
     */
    public function describe($object, array $options = []);

}
