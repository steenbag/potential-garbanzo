<?php namespace Steenbag\Tubes\Token\Claim;

use Steenbag\Tubes\Token\ValidationData;

class ContainedEqualsTo extends Claim implements  ClaimInterface, Validatable
{

    /**
     * Returns if claim is valid according to passed-in data.
     *
     * @param ValidationData $data
     * @return bool
     */
    public function validate(ValidationData $data)
    {
        if ($data->has($this->getName())) {
            return in_array($this->getValue(), $data->get($this->getName()));
        }

        return true;
    }

}
