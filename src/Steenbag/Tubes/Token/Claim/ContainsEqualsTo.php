<?php namespace Steenbag\Tubes\Token\Claim;

use Steenbag\Tubes\Token\ValidationData;

class ContainsEqualsTo extends Claim implements ClaimInterface, Validatable
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
            $myValue = $this->getValue();
            $passedValue = $data->get($this->getName());
            $myValue = is_array($myValue) ? $myValue : [$myValue];

            return in_array($passedValue, $myValue);
        }

        return true;
    }

}
