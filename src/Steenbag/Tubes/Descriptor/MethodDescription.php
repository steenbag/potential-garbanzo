<?php namespace Steenbag\Tubes\Descriptor;

use Steenbag\Tubes\DocBlock\DocBlock;
use Steenbag\Tubes\Transformer\Transformer;

class MethodDescription
{

    protected $name;

    protected $description;

    protected $params;

    protected $filters;

    protected $permissions;

    protected $returns;

    protected $throws;

    protected $examples;

    protected $reflection;

    protected $deprecated;

    protected $transformer;

    public function __construct($methodName, DocBlock $docBlock, $reflection = null)
    {
        $this->name = $methodName;
        $this->description = $docBlock->desc;
        $this->reflection = $reflection;
        $this->params = $this->parseParams($docBlock->tag('param'));
        $this->filters = $this->parseFilters($docBlock->tag('filter'));
        $this->returns = $this->parseReturns($docBlock->tag('return'));
        $this->throws = $this->parseThrows($docBlock->tag('throws'));
        $this->examples = $docBlock->hasTag('example') ? $docBlock->tag('example') : [];
        $this->permissions = $docBlock->hasTag('permission') ? $this->parsePermissions($docBlock->tag('permission')) : [];
        $this->deprecated = $docBlock->hasTag('deprecated');
        $this->transformer = $docBlock->hasTag('transformer') ? $this->parseTransformer($docBlock->tag('transformer')) : null;
    }

    protected function parseParams(array $params = null)
    {
        $_params = [];

        $params = $params ?: [];

        foreach ($params as $param) {
            $reflection = $this->getReflectedParameter($param['var']);
            $_params []= new ParamDescription($param['var'], $param['type'], $param['desc'], $reflection);
        }

        return $_params;
    }

    protected function parseFilters(array $filters = null)
    {
        $_filters = [];

        $filters = $filters ?: [];

        foreach ($filters as $filter) {
            $reflection = $this->getReflectedParameter($filter['var']);
            $_filters []= new ParamDescription($filter['var'], $filter['type'], $filter['desc']);
        }

        return $_filters;
    }

    protected function parsePermissions(array $permissions = [])
    {
        $_permissions = [];
        foreach ($permissions as $permission) {
            $_permissions []= [
                'name' => $permission['name'],
                'description' => $permission['description']
            ];
        }

        return $_permissions;
    }

    protected function parseTransformer(array $transformer = [])
    {
        $transformer = array_shift($transformer);
        if ($transformer && class_exists($transformer)) {
            return new $transformer;
        }
    }

    protected function getReflectedParameter($param)
    {
        if ($this->reflection) {
            foreach ($this->reflection->getParameters() as $parameter) {
                if (("$" . $parameter->name) === $param) {
                    return $parameter;
                }
            }
        }
    }

    protected function parseReturns(array $returns = null)
    {
        $_returns = [];

        $returns = $returns ?: [];

        foreach ($returns as $return) {
            $_returns []= new ReturnDescription($return['type'], $return['desc']);
        }

        return $_returns;
    }

    protected function parseThrows(array $throws = null)
    {
        $_throws = [];

        $throws = $throws ?: [];

        foreach ($throws as $throw) {
            $_throws []= new ExceptionDescription($throw);
        }

        return $_throws;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Steenbag\Tubes\DocBlock\Array
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return void
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * @return void
     */
    public function getThrows()
    {
        return $this->throws;
    }

    /**
     * @return array|\Steenbag\Tubes\DocBlock\Array
     */
    public function getExamples()
    {
        return $this->examples;
    }

    /**
     * Get the requested example.
     *
     * @param int $i
     * @return mixed|null
     */
    public function getExample($i = 0)
    {
        return count($this->examples) && isset($this->examples[$i]) ? $this->examples[$i] : null;
    }

    /**
     * Return the given example as HTML.
     *
     * @param int $i
     * @param string $basePath
     * @return string
     * @throws \ReflectionException
     */
    public function getExampleAsHtml($i = 0, $basePath = '')
    {
        $example = $this->getExample($i);
        if (!$example) {
            return;
        }
        $path = $basePath . DIRECTORY_SEPARATOR . ltrim($example, DIRECTORY_SEPARATOR);
        // We're dealing with a file.
        if (strpos($example, '.')) {
            if (file_exists($path)) {
                $contents = file_get_contents($path);

                return $contents;
            }
        } elseif (strpos($example, '@')) {
            // Otherise we're dealing with a class definition.
            list($class, $method) = explode('@', $example);
            if (class_exists($class) && method_exists($class, $method)) {
                $reflection = new \ReflectionMethod($class, $method);
                $file = $reflection->getFileName();
                $startLine = $reflection->getStartLine();
                $endLine = $reflection->getEndLine();
                $contents = explode("\n", file_get_contents($file));
                $body = array_slice($contents, $startLine, $endLine - $startLine);
                if (trim($body[0]) == '{') {
                    array_shift($body);
                }
                if (trim($body[count($body) - 1]) == '}') {
                    array_pop($body);
                }
                return implode("\r\n", $body);
            }
        }
    }

    /**
     * Returns true if this method has at least one example.
     *
     * @return bool
     */
    public function hasExamples()
    {
        return count($this->examples) > 0;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return boolean
     */
    public function isDeprecated()
    {
        return $this->deprecated;
    }

    /**
     * @return Transformer
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

}
