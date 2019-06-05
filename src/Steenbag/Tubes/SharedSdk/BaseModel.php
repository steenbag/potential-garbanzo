<?php namespace Steenbag\Tubes\SharedSdk;

use Steenbag\Tubes\Support\StringHelper;
use InvalidArgumentException;
use Thrift\Base\TBase;
use Thrift\Type\TType;

class BaseModel
{

    use DateParser;

    protected $thriftModel;

    protected $attributes = [];

    protected $dateTypes = ['createdAt', 'updatedAt', 'deletedAt'];

    protected $currencyTypes = [];

    protected $enums = [];

    protected $relations = [];

    public function __construct(TBase $thriftModel)
    {
        $this->thriftModel = $thriftModel;
        $this->bootstrap();
    }

    /**
     * Create a new instance of our model.
     *
     * @param TBase $thriftModel
     * @return static
     */
    public static function make(TBase $thriftModel)
    {
        return new static($thriftModel);
    }

    /**
     * Hydrate all of the properties into our attributes array.
     */
    protected function bootstrap()
    {
        $spec = $this->getSpec();
        foreach ($spec as $property) {
            $propertyName = $property['var'];
            $propertyType = $property['type'];
            $this->set($propertyName, $this->thriftModel->$propertyName, $propertyType);
        }
    }

    /**
     * Set the given member.
     *
     * @param $attribute
     * @param $value
     * @param null $type
     * @return mixed
     */
    public function set($attribute, $value, $type = null)
    {
        $type = $type ?: $this->getFieldSpec($attribute);
        $mutator = $this->getMutator($attribute, 'set');
        if (method_exists($this, $mutator)) {
            $value = $this->$mutator($value);
        }

        if (!is_null($value)) {
            if ($this->isRelation($attribute)) {
                $value = $this->getRelation($attribute, $value, $type);
            }

            if ($this->isDate($attribute)) {
                $value = $this->getDate($value);
            }

            if ($this->isCurrency($attribute)) {
                $value = $this->getCurrency($value);
            }

            if ($this->isEnum($attribute)) {
                $value = $this->setEnum($attribute, $value);
            }
        }

        $attribute = StringHelper::camelCase($attribute);

        return $this->attributes[$attribute] = $value;
    }

    public function __set($attribute, $value)
    {
        return $this->set($attribute, $value);
    }

    /**
     * Get the value of the given member.
     *
     * @param $attribute
     * @return mixed
     */
    public function get($attribute)
    {

        $mutator = $this->getMutator($attribute);
        if (method_exists($this, $mutator)) {
            $value = isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
            return $this->$mutator($value);
        }
        $attribute = StringHelper::camelCase($attribute);

        if ($this->isDate($attribute)) {
            return isset($this->attributes[$attribute]) ? $this->attributes[$attribute]->format(\DateTime::ISO8601) : null;
        }

        return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
    }

    protected function getMutator($attribute, $direction = 'get')
    {
        return $direction . ucfirst(StringHelper::camelCase($attribute)) . 'Field';
    }

    public function __get($attribute)
    {
        return $this->get($attribute);
    }

    /**
     * Add support for dynamic getters and setters.
     *
     * @param $method
     * @param array $args
     * @return mixed|null
     */
    public function __call($method, array $args = [])
    {
        if (strpos($method, 'get') === 0) {
            $baseKey = substr($method, 3);
            return $this->get($baseKey);
        }

        throw new \BadMethodCallException("Method {$method} is not defined on this class.");
    }

    /**
     * Parse the given date attribute.
     *
     * @param $value
     * @return \DateTime
     */
    public function getDate($value)
    {
        return $this->parseDate($value);
    }

    /**
     * Returns true if the passed-in field should be treated as a date.
     *
     * @param $attribute
     * @return bool
     */
    public function isDate($attribute)
    {
        return in_array($attribute, $this->getDateTypes()) || in_array(StringHelper::camelCase($attribute), $this->getDateTypes());
    }

    /**
     * Return all of the date fields.
     *
     * @return array
     */
    public function getDateTypes()
    {
        return $this->dateTypes;
    }

    /**
     * Parse the given date attribute.
     *
     * @param $value
     * @return \DateTime
     */
    public function getCurrency($value)
    {
        return floatval($value);
    }

    /**
     * Returns true if the passed-in field should be treated as currency.
     *
     * @param $attribute
     * @return bool
     */
    public function isCurrency($attribute)
    {
        return in_array($attribute, $this->getCurrencyTypes());
    }

    /**
     * Return all of the currency fields.
     *
     * @return array
     */
    public function getCurrencyTypes()
    {
        return $this->currencyTypes;
    }

    /**
     * Returns true if the pass-in field is part of an enum.
     *
     * @param $attribute
     * @return bool
     */
    public function isEnum($attribute)
    {
        return array_key_exists($attribute, $this->getEnums());
    }

    /**
     * Return all of the Enum fields.
     *
     * @return array
     */
    public function getEnums()
    {
        return $this->enums;
    }

    /**
     * Validate and get the string value of an enum.
     *
     * @param $enum
     * @param $raw
     * @return string
     */
    public function setEnum($enum, $raw)
    {
        $this->validateEnumValue($enum, $raw);

        return $this->getEnumString($enum, $raw);
    }

    /**
     * Validate the value of an enum field.
     *
     * @param $enum
     * @param $raw
     */
    protected function validateEnumValue($enum, $raw)
    {
        $values = $this->getEnumValues($enum);

        if (! array_key_exists($raw, $values)) {
            $raw = is_null($raw) ? 'NULL' : $raw;
            throw new InvalidArgumentException("{$raw} is not a valid {$enum} value.");
        }
    }

    /**
     * Get the string value of an enum constant.
     *
     * @param $enum
     * @param $raw
     * @return null
     */
    protected function getEnumString($enum, $raw)
    {
        $values = $this->getEnumValues($enum);

        return isset($values[$raw]) ? $values[$raw] : null;
    }

    /**
     * Get the values belonging to a given Thrift enum.
     *
     * @param $enum
     * @return array
     */
    protected function getEnumValues($enum)
    {
        $enumClass = isset($this->enums[$enum]) ? $this->enums[$enum] : null;
        if ($enumClass && class_exists($enumClass)) {
            return $enumClass::$__names;
        }

        return [];
    }

    /**
     * Returns true if the passed-in attribute is a relation.
     *
     * @param $attribute
     * @return bool
     */
    protected function isRelation($attribute)
    {
        return array_key_exists($attribute, $this->getRelations());
    }

    /**
     * Get all of the defined relations.
     *
     * @return array
     */
    protected function getRelations()
    {
        return $this->relations;
    }

    /**
     * Return the SDK Class to use to hydrate the individual members of the relation.
     *
     * @param $relation
     * @return mixed|null
     */
    protected function getRelationClass($relation)
    {
        $relations = $this->getRelations();
        $relativeClass = isset($relations[$relation]) ? $relations[$relation] : null;
        if ($relativeClass && class_exists($relativeClass)) {
            return $relativeClass;
        }
    }

    /**
     * Hydrate the given relation.
     *
     * @param $attribute
     * @param $value
     * @param $type
     * @return mixed
     * @throws
     */
    protected function getRelation($attribute, $value, $type)
    {
        $relativeClass = $this->getRelationClass($attribute);
        if (!$relativeClass) {
            return $value;
        }
        switch ($type) {
            case TType::STRUCT:
                return $relativeClass::make($value);
            case TType::LST:
                return $this->getMultiRelation($relativeClass, $value);
        }

        throw new InvalidArgumentException("Relative type ({$attribute}){$type} isn't supported.");
    }

    /**
     * Return an array of relations for a collection-type relation.
     *
     * @param $relativeClass
     * @param $value
     * @return array
     */
    protected function getMultiRelation($relativeClass, $value)
    {
        $result = [];

        if (! $value) {
            $value = [];
        }

        foreach ($value as $key => $item) {
            $result[$key] = $relativeClass::make($item);
        }

        return $result;
    }

    /**
     * Return the Thrift spec for the base Thrift model.
     *
     * @return mixed
     */
    protected function getSpec()
    {
        $class = get_class($this->thriftModel);

        return $class::$_TSPEC;
    }

    /**
     * Get the spec for a given field.
     *
     * @param $field
     * @return mixed
     */
    protected function getFieldSpec($field)
    {
        $field = StringHelper::snake_case($field);
        $spec = $this->getSpec();
        foreach ($spec as $item) {
            if ($item['var'] == $field) {
                return $item;
            }
        }
    }

    public function getAttributes()
    {
        $attrs = [];
        $keys = array_keys($this->attributes);
        foreach ($keys as $attribute) {
            $attrs[$attribute] = $this->get($attribute);
        }

        return $attrs;
    }

}
