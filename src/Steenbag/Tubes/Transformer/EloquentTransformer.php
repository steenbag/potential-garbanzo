<?php namespace Steenbag\Tubes\Transformer;

use Steenbag\Tubes\Filter\Filter;
use Steenbag\Tubes\Filter\FilterCollection;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentTransformer extends Transformer
{

    /**
     * @var array Contains fields used for select, but not transformed.
     */
    protected $selects;

    /**
     * @var array Contains the relations to load.
     */
    protected $relations;

    /**
     * @var array Contains relative transformers once instantiated.
     */
    protected $transformers;

    /**
     * @var array Contains normalized relation definitions.
     */
    protected $relationDefinitions;

    /**
     * @var array Contains the available relations to load.
     */
    protected $availableRelations = [];

    /**
     * @var array Contains the fields that are required.
     */
    protected $requiredFields = ['id'];

    /**
     * @var array Contains a list of date fields to be transformed into atom strings.
     */
    protected $dateFields = ['created_at', 'updated_at', 'deleted_at'];

    public function setSelects(array $selects)
    {
        $this->selects = $selects;
    }

    public function setRelations(array $relations)
    {
        $this->relations = $relations;
    }

    public function getRelations()
    {
        if (!isset($this->relations)) {
            $this->getFields();
        }
        return $this->relations;
    }

    public function getSelectFields()
    {
        $selectFields = array_filter(array_map(function ($field) {
            if (array_key_exists($field, $this->relations)) {
                return $this->getRelationDefinition($field, 'select_fields', []);
            }
            return $this->getFieldTranslation($field);
        }, array_merge($this->getFields(), array_keys($this->getRelations()))));

        return array_unique(array_flatten(array_merge($selectFields, $this->selects)));
    }

    public function getLoadRelations()
    {
        $loadRelations = [];
        foreach ($this->getRelations() as $relationField => $relativeFields) {
            $transformer = $this->getRelationTransformer($relationField);
            $relation = $this->getRelationDefinition($relationField, 'relation');
            $relationSelectFields = $this->getRelationDefinition($relationField, 'relation_select_fields', []);

            $selectType = array_pull($relativeFields, 'default', false) ? 'include' : 'fields';
            $selectFields = array_unique(array_merge($relationSelectFields, array_pull($relativeFields, 'select', [])));
            $relativeFields = [$selectType => implode(',', $relativeFields)] + ($selectFields ? ['select' => implode(',', $selectFields)] : []);
            $transformer->parseFields($relativeFields);

            if ($transformer instanceof EloquentTransformer) {
                $loadRelations[camel_case($relation)] = function ($query) use ($transformer) {
                    $query->select($transformer->getSelectFields());
                    $query->with($transformer->getLoadRelations());
                };
            } else {
                $loadRelations [] = $relation;
            }
        }

        return $loadRelations;
    }

    /**
     * Set return fields from provided filters.
     *
     * @param array|FilterCollection $filters
     */
    public function parseFields($filters)
    {
        parent::parseFields($filters);
        $availableFields = $this->getAvailableFields();

        $_fields = $_relations = $_selects = [];
        foreach ($this->fields as $field) {
            $field = trim($field);
            $checkField = $field;
            $relativeField = null;
            if (strpos($field, '.')) {
                // Dealing with a relative.
                $p = explode('.', $field);
                $checkField = array_shift($p);
                if ($p) {
                    $relativeField = implode('.', $p);
                }
            }
            if (array_key_exists($checkField, $this->availableRelations)) {
                if (!isset($_relations[$checkField])) {
                    $_relations[$checkField] = [];
                    if (in_array($checkField, $this->fields)) {
                        $_relations[$checkField]['default'] = true;
                    }
                }
                if ($relativeField) {
                    $_relations[$checkField] []= $relativeField;
                }
            } else {
                if (in_array($checkField, $availableFields)) {
                    $_fields []= $field;
                }
            }
        }

        // check if we need to load any relations based on a field option
        foreach (array_keys($this->availableRelations) as $relationField) {
            $requiredBys = $this->getRelationDefinition($relationField, 'required_by', []);
            foreach ($requiredBys as $requiredBy => $additionalSelectFields) {
                if (is_numeric($requiredBy)) {
                    $requiredBy = $additionalSelectFields;
                    $additionalSelectFields = [];
                }
                if (in_array($requiredBy, $_fields)) {
                    $_relation = array_get($_relations, $relationField, []);
                    $_relation['select'] = array_unique(array_merge(array_get($_relation, 'select', []), (array) $additionalSelectFields));
                    $_relations[$relationField] = $_relation;
                }
            }
        }

        if (is_array($filters) && array_key_exists('select', $filters)) {
            $_selects = explode(',', array_get($filters, 'select'));
        }

        $this->setFields($_fields);
        $this->setRelations($_relations);
        $this->setSelects($_selects);
    }

    /**
     * Retrieve the specified field values from the resource.
     *
     * @param mixed $resource
     * @return array
     */
    public function getFieldValues($resource)
    {
        $fieldValues = parent::getFieldValues($resource);
        foreach ($this->getRelations() as $relationField => $relativeFields) {
            $includeRelation = count(array_except($relativeFields, ['select'])) > 0;
            if ($includeRelation) {
                $mutator = 'get' . studly_case($relationField) . 'Relation';
                if (method_exists($this, $mutator)) {
                    $value = call_user_func([$this, $mutator], $resource);
                } else {
                    $transformer = $this->getRelationTransformer($relationField);
                    $relation = $this->getRelationDefinition($relationField, 'relation');
                    $relation = $resource->{$relation};
                    if (is_array($relation) || $relation instanceof \Traversable) {
                        $value = [];
                        foreach ($relation as $item) {
                            $value []= $transformer->transform($item);
                        }
                    } else {
                        $value = $transformer->transform($relation);
                    }
                }

                $fieldValues[$relationField] = $value;
            }
        }

        return $fieldValues;
    }

    public function getAvailableFields()
    {
        return array_merge(parent::getAvailableFields(), array_keys($this->availableRelations));
    }

    protected function getRelationDefinition($relationField, $definitionField = null, $default = null)
    {
        if (!isset($this->relationDefinitions[$relationField])) {
            $definition = array_get($this->availableRelations, $relationField);
            if (is_array($definition)) {
                $transformer = array_get($definition, 'transformer');
                $relation = array_get($definition, 'relation');
                $requiredBy = array_get($definition, 'required_by');
                $selectFields = array_get($definition, 'select_fields');
                $relationSelectFields = array_get($definition, 'relation_select_fields');
            } else {
                $transformer = $definition;
            }

            $this->relationDefinitions[$relationField] = [
                'transformer' => $transformer,
                'relation' => isset($relation) ? $relation : $relationField,
                'required_by' => isset($requiredBy) ? (array) $requiredBy : [],
                'select_fields' => isset($selectFields) ? (array) $selectFields : ["{$relationField}_id"],
                'relation_select_fields' => isset($relationSelectFields) ? (array) $relationSelectFields : []
            ];
        }

        if ($definitionField) {
            return array_get($this->relationDefinitions[$relationField], $definitionField, $default);
        }

        return $this->relationDefinitions[$relationField];
    }

    /**
     * @param $relationField
     * @return EloquentTransformer
     * @throws \Exception
     */
    public function getRelationTransformer($relationField)
    {
        if (!isset($this->transformers[$relationField])) {
            $transformerClass = $this->getRelationDefinition($relationField, 'transformer');
            if (!(isset($transformerClass) && class_exists($transformerClass))) {
                throw new \Exception("Transformer class not defined for relation: {$relationField}");
            }

            $this->transformers[$relationField] = new $transformerClass;
        }

        return $this->transformers[$relationField];
    }

}
