<?php namespace Steenbag\Tubes\Descriptor;

use Steenbag\Tubes\WebService\ServiceInterface;

abstract class Descriptor implements \Steenbag\Tubes\Contract\Descriptor
{


    /**
     * Describe the passed-in service.
     *
     * @param mixed $object
     * @param array $options
     * @return mixed
     */
    public function describe($object, array $options = [])
    {
        switch (true) {
            case $object instanceof ServiceInterface:
                return $this->describeService($object, $options);
            default:
                throw new \InvalidArgumentException(sprintf('Object of type "%s" is not describable.', get_class($object)));
        }
    }

    /**
     * Describe a Tubes Service.
     *
     * @param ServiceInterface $object
     * @param array $options
     * @return mixed
     */
    abstract public function describeService(ServiceInterface $object, array $options = []);

}
