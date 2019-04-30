<?php namespace Steenbag\Tubes\Descriptor;

use Steenbag\Tubes\Support\StringHelper;
use Steenbag\Tubes\WebService\ServiceInterface;

class JsonDescriptor extends Descriptor
{

    /**
     * Describe a Tubes Service.
     *
     * @param ServiceInterface $object
     * @param array $options
     * @return mixed
     */
    public function describeService(ServiceInterface $object, array $options = [])
    {
        $basePath = isset($options['base_path']) ? $options['base_path'] : '';

        $description = new ServiceDescription($object);
        $service = [
            'name' => $description->getName(),
            'slug' => StringHelper::camelCase($description->getName()),
            'description' => $description->getDescription(),
            'version' => $description->getVersion(),
            'methods' => [],
            'clients' => $description->getClients()
        ];

        /**
         * @var string $name
         * @var MethodDescription $method
         * @var ParamDescription $param
         */
        foreach ($description->getMethods() as $name => $method) {
            $_method = [
                'name' => $method->getName(),
                'slug' => StringHelper::camelCase($method->getName()),
                'description' => $method->getDescription(),
                'params' => [],
                'examples' => [],
                'filters' => [],
                'returns' => $this->processReturns($method->getReturns()),
                'permissions' => $method->getPermissions(),
                'deprecated' => $method->isDeprecated()
            ];
            if ($method->getTransformer()) {
                $transformer = $method->getTransformer();
                $_method['transformer'] = [
                    'name' => get_class($transformer)
                ];
                $_method['result_fields'] = [
                    'all' => $transformer->getAvailableFields(),
                    'default' => $transformer->getDefaultFields()
                ];
            }
            foreach ($method->getParams() as $param) {
                $_param = [
                    'name' => $param->getName(),
                    'type' => $param->getType(),
                    'default' => $param->getDefault(),
                    'description' => $param->getDescription(),
                    'is_required' => $param->isRequired()
                ];
                $_method['params'] []= $_param;
            }
            foreach ($method->getFilters() as $filter) {
                $_filter = [
                    'name' => $filter->getName(),
                    'type' => $filter->getType(),
                    'default' => $filter->getDefault(),
                    'description' => $filter->getDescription()
                ];
                $_method['filters'] []= $_filter;
            }
            if ($method->hasExamples()) {
                foreach ($method->getExamples() as $i => $example) {
                    $html = $method->getExampleAsHtml($i, $basePath);
                    if ($html && strlen($html)) {
                        $_method['examples'] []= $html;
                    }
                }
            }
            $service['methods'] []= $_method;
        }

        return $service;
    }

    protected function processReturns(array $returns = [])
    {
        $_returns = [];
        foreach ($returns as $return) {
            $_returns []= [
                'type' => $return->getType(),
                'description' => $return->getDescription()
            ];
        }
        
        return $_returns;
    }

}
