<?php namespace Steenbag\Tubes\Filter;

use Steenbag\Tubes\Support\StringHelper;

class FilterCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{

    protected $filters = [];

    protected static $fromExtensions = [];

    protected static $toExtensions = [];

    public function __construct(array $filters = [])
    {
        if (count($filters)) {
            $this->push($filters);
        }
    }

    /**
     * Return all of the underlying filters.
     *
     * @return array
     */
    public function all()
    {
        return $this->filters;
    }

    /**
     * Clear all entered filters.
     *
     * @return $this
     */
    public function clear()
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Return true if the given filter is in the collection.
     *
     * @param $filter
     * @return bool
     */
    public function has($filter)
    {
        return array_key_exists($filter, $this->filters);
    }

    /**
     * Get the given filter.
     *
     * @param $filter
     * @return Filter
     */
    public function get($filter)
    {
        return isset($this->filters[$filter]) ? $this->filters[$filter] : null;
    }

    /**
     * Return the associative array of the filters.
     */
    public function getDictionary()
    {
        return $this->filters;
    }

    /**
     * Add a filter to the collection.
     */
    public function push()
    {
        $args = func_get_args();
        if ($args[0] instanceof FilterCollection) {
            foreach ($args[0] as $filter) {
                $this->push($filter);
            }
        } elseif ($args[0] instanceof Filter) {
            $filter = $args[0];
            $this->filters[$filter->getField()] = $filter;
        } elseif (is_array($args[0])) {
            // Handles input in the format [['field' => 'my_field', 'value' => 'search value']]
            if ($this->is_assoc($args[0]) && isset($args[0]['field'])) {
                $filter = Filter::fromArray($args[0]);
                $this->filters[$filter->getField()] = $filter;
            // Handles input in the form ['my_field' => 'search value', 'second_field' => 'second value']
            } elseif ($this->is_assoc($args[0])) {
                foreach ($args[0] as $k => $v) {
                    $this->filters[$k] = $v instanceof Filter ? $v : new Filter($k, $v);
                }
            // Otherwise, recursively call it's self.
            } else {
                foreach ($args[0] as $filter) {
                    $this->push($filter);
                }
            }
        }

        return $this;
    }

    /**
     * Merge the current filters
     *
     * @param FilterCollection $newFilters
     * @return FilterCollection
     */
    public function merge(FilterCollection $newFilters)
    {
        return new static(array_merge($this->all(), $newFilters->all()));
    }

    /**
     * Create a new instance based on the passed-in array.
     *
     * @param array $filters
     * @return static
     */
    public static function fromArray(array $filters)
    {
        return new static($filters);
    }

    /**
     * Return the base filter array.
     *
     * @return array
     */
    public function toArray()
    {
        $_filters = [];

        foreach ($this->filters as $filter) {
            $_filters[$filter->getField()] = $filter->toArray();
        }

        return $_filters;
    }

    /**
     * Returns the filters as a simple key => value array.
     * Does not support advanced operators.
     */
    public function toSimpleArray()
    {
        $_filters = [];

        foreach ($this->filters as $filter) {
            $_filters[$filter->getField()] = $filter->getValue();
        }

        return $_filters;
    }

    /**
     * Dynamically add 'from' methods into the class.
     *
     * This allows us to dynamically add in support for things like hydrating from Thrift or a querystring.
     *
     * Methods added through here will be called via "from" + $method.
     *
     * @param String $method
     * @param Closure $callable
     */
    public static function extendFrom($method, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Second parameter must be callable!");
        }
        static::$fromExtensions[$method] = $callable;
    }

    /**
     * Dynamically add 'to' methods into the class.
     *
     * This allows us to dynamically add in support for things like transforming to Thrift or a querystring.
     *
     * Methods added through here will be called via "from" + $method.
     *
     * @param String $method
     * @param Closure $callable
     */
    public static function extendTo($method, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Second parameter must be callable!");
        }
        static::$toExtensions[$method] = $callable;
    }

    public static function __callStatic($method, $args)
    {
        if (strpos($method, 'from') === 0) {
            $baseName = StringHelper::camelCase(str_replace('from', '', $method));
            if (array_key_exists($baseName, static::$fromExtensions)) {
                array_unshift($args, new static);
                return call_user_func_array(static::$fromExtensions[$baseName], $args);
            }
        }
        if (strpos($method, 'to') === 0) {
            $baseName = StringHelper::camelCase(str_replace('to', '', $method));
            if (array_key_exists($baseName, static::$toExtensions)) {
                array_unshift($args, new static);
                return call_user_func_array(static::$toExtensions[$baseName], $args);
            }
        }
        throw new \BadMethodCallException("Method {$method} doesn't exist.");
    }

    /**
     * Return the ArrayIterator for the Collection.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->filters);
    }

    /**
     * Return the number of filters in the collection.
     */
    public function count()
    {
        return count($this->filters);
    }

    /**
     * Whether a filter exists.
     *
     * @param mixed $filter
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($filter)
    {
        return isset($this->filters[$filter]);
    }

    /**
     * Filter to retrieve.
     *
     * @param mixed $filter
     * @return mixed
     */
    public function offsetGet($filter)
    {
        return $this->filters[$filter];
    }

    /**
     * Filter to set.
     *
     * @param mixed $filter
     * @param mixed $value
     * @return void
     */
    public function offsetSet($filter, $value)
    {
        $this->filters[$filter] = $value;
    }

    /**
     * Filter to unset.
     *
     * @param mixed $filter
     * @return void
     */
    public function offsetUnset($filter)
    {
        unset($this->filters[$filter]);
    }

    /**
     * Return true if the passed array is an assocative array. Only checks the top level of the array.
     *
     * @param array $array
     * @return bool
     */
    protected function is_assoc($array)
    {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v)
                return true;
        }
        return false;
    }

}
