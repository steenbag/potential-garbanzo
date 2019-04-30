<?php namespace Steenbag\Tubes\Transformer;

use Steenbag\Tubes\Filter\FilterCollection;
use Underscore\Types\Arrays;
use Underscore\Types\Strings;

abstract class Transformer
{

    /**
     * @var array Contains the fields to transform.
     */
    protected $fields;

    /**
     * @var array Contains the default fields to transform.
     */
    protected $defaultFields = [];

    /**
     * @var array Contains the fields that are required.
     */
    protected $requiredFields = [];

    /**
     * @var array Contains the available fields to transform.
     */
    protected $availableFields = [];

    /**
     * @var array Contains any field translations.
     */
    protected $fieldTranslations = [];

    /**
     * @var array Contains a list of date fields to be transformed into atom strings.
     */
    protected $dateFields = [];

    /**
     * Set the fields to be returned by the transformer.
     *
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Retrieve the list of fields to be returned by the transformer.
     *
     * @return array
     */
    public function getFields()
    {
        if (!isset($this->fields)) {
            $this->defaultFields = array_intersect($this->defaultFields, $this->getAvailableFields());
            $this->parseFields([]);
        }
        return $this->fields;
    }

    /**
     * Set return fields from provided filters.
     *
     * @param array|FilterCollection $filters
     */
    public function parseFields($filters)
    {
        if (is_array($filters)) {
            $filters = FilterCollection::fromArray($filters);
        }

        $fields = $filters->has('fields') ? explode(',', $filters->get('fields')->getValue()) : $this->defaultFields;
        if ($filters->has('include')) {
            $include = explode(',', $filters->get('include')->getValue());
            $fields = array_merge($fields, $include);
        }
        if ($filters->has('exclude')) {
            $exclude = explode(',', $filters->get('exclude')->getValue());
            $fields = array_diff($fields, $exclude);
        }

        $this->setFields(array_unique(array_merge($fields, $this->requiredFields)));
    }

    /**
     * Retrieves an array of subordinate fields to the given relation.
     *
     * @param $relation
     * @return array
     */
    public function parseRelativeFields($relation)
    {
        $relativeFields = [];
        foreach ($this->fields as $field) {
            if (strpos($field, "{$relation}.") === 0) {
                $p = explode('.', $field, 2);
                $relativeFields []= $p[1];
            }
        }

        return $relativeFields;
    }

    /**
     * Extract the fields to send to a sub-transformer.
     *
     * @param $relation
     * @return array
     */
    public function getRelativeFields($relation)
    {
        $relativeFields = $this->parseRelativeFields($relation);
        if (empty($relativeFields)) {
            return [];
        }
        $relativeFields = implode(',', $relativeFields);

        if (in_array($relation, $this->defaultFields) || (in_array($relation, $this->fields) && empty($relativeFields))) {
            return ['include' => $relativeFields];
        }

        return ['fields' => $relativeFields];
    }

    /**
     * Returns the translated field name for internal use.
     *
     * @param string $field
     * @return string
     */
    protected function getFieldTranslation($field)
    {
        $fieldDefinition = Arrays::get($this->fieldTranslations, $field, $field);
        return is_array($fieldDefinition) ? Arrays::get($fieldDefinition, 'field', $field) : $fieldDefinition;
    }

    /**
     * Returns the cast value for the field (if any).
     *
     * @param string $field
     * @return null|string
     */
    protected function getFieldCast($field)
    {
        if (in_array($field, $this->dateFields)) {
            return 'date';
        }

        $fieldDefinition = Arrays::get($this->fieldTranslations, $field, $field);
        return is_array($fieldDefinition) ? Arrays::get($fieldDefinition, 'cast') : null;
    }

    /**
     * Retrieve the specified field values from the resource.
     *
     * @param mixed $resource
     * @return array
     */
    public function getFieldValues($resource)
    {
        $fieldValues = [];
        foreach ($this->getFields() as $field) {
            Arrays::set($fieldValues, $field, $this->getFieldValue($resource, $field));
        }

        return $fieldValues;
    }

    /**
     * Retrieves the specified field value from the resource.
     *
     * @param mixed $resource
     * @param string $field
     * @return mixed
     */
    protected function getFieldValue($resource, $field)
    {
        $mutator = 'get' . Strings::toPascalCase(str_replace('.', ' ', $field)) . 'Field';
        if (method_exists($this, $mutator)) {
            $value = call_user_func([$this, $mutator], $resource);
        } else {
            $value = null;
            $cast = $this->getFieldCast($field);
            $field = $this->getFieldTranslation($field);
            if (is_array($resource)) {
                $value = Arrays::get($resource, $field);
            } else {
                $p = explode('.', $field);
                foreach ($p as $idx => $field) {
                    $value = $resource->{$field};
                    if ($idx + 1 < count($p)) {
                        $resource = $value;
                    }
                }
            }

            switch ($cast) {
                case 'bool':
                    $value = (bool) $value;
                    break;
                case 'int':
                    $value = (int) $value;
                    break;
                case 'float':
                    $value = (float) $value;
                    break;
                case 'string':
                    $value = (string) $value;
                    break;
                case 'date':
                    $value = $value->toAtomString();
                    break;
            }
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getDefaultFields()
    {
        return $this->defaultFields;
    }

    /**
     * @return array
     */
    public function getAvailableFields()
    {
        return $this->availableFields;
    }

    /**
     * Perform the data transformation on the resource.
     *
     * @param mixed $resource
     * @return mixed
     */
    abstract public function transform($resource);

}
