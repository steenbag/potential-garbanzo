<?php namespace Steenbag\Tubes\Token\Claim;

use Steenbag\Tubes\Token\ValidationData;

interface Validatable
{

    /**
     * Returns if claim is valid according to passed-in data.
     *
     * @param ValidationData $data
     * @return bool
     */
    public function validate(ValidationData $data);

}
