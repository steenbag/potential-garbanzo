<?php namespace Steenbag\Tubes\Filter;

class Filter
{

    protected $field;

    protected $operator = '=';

    protected $value;

    public function __construct()
    {
        switch (func_num_args()) {
            // Three args indicate field, operator, value.
            case 3:
                $this->field = func_get_arg(0);
                $this->operator = func_get_arg(1);
                $this->value = func_get_arg(2);
                break;
            // Two args indicates field, value.
            case 2:
                $this->field = func_get_arg(0);
                $this->value = func_get_arg(1);
                break;
            // A Single arg is jus tthe field name.
            case 1:
                $this->field = func_get_arg(0);
                break;
            case 0:
                // Allow us to just new up an instance.
                break;
            default:
                $count = func_num_args();
                throw new \InvalidArgumentException("Incorrect number of arguments ({$count}).");
        }
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed|string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed|string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function toArray()
    {
        return [
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value
        ];
    }

    public static function fromArray(array $props)
    {
        $instance = new static;
        foreach (['field', 'operator', 'value'] as $field) {
            if (isset($props[$field])) {
                $instance->$field = $props[$field];
            }
        }

        return $instance;
    }

}
