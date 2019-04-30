<?php namespace Steenbag\Tubes\Generator;

use Illuminate\Filesystem\Filesystem;

class NgServiceGenerator
{

    protected $files;

    protected $outputPath;

    protected $baseDependencyPath;

    protected $dependencyExt;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->outputPath = app_path('assets/scripts/thrift/gen-js');
        $this->baseDependencyPath = "";
        $this->dependencyExt = 'min';
    }

    public function setOutputPath($path)
    {
        $this->outputPath = $path;
    }

    public function setDependencyPath($path)
    {
        $this->baseDependencyPath = $path;
    }

    public function setDependencyExt($ext)
    {
        $this->dependencyExt = $ext;
    }

    /**
     * Create a new client service.
     * @param string $serviceName
     * @param string $thriftServiceName
     * @param string $thriftName
     * @param string $thriftFile
     * @param array $serviceDefinition
     * @param $filename
     * @return mixed
     */
    public function create($serviceName, $thriftServiceName, $thriftName, $thriftFile, array $serviceDefinition, $filename)
    {
        $path = $this->getPath($filename);
        $stub = $this->getStub();
        $this->files->put($path, $this->populateStub($serviceName, $thriftServiceName, $thriftName, $thriftFile, $serviceDefinition, $stub));

        return $path;
    }

    /**
     * Get the path to the stubs dir.
     *
     * @return mixed
     */
    protected function getStubPath()
    {
        return realpath(__DIR__ . '/../../../stubs/');
    }

    /**
     * Get the paht to store output files at.
     *
     * @param $path
     * @return string
     */
    protected function getOutputPath($path)
    {
        return $this->outputPath . (ends_with($this->outputPath, '/') ? '' : '/') . $path;
    }

    /**
     * Get the path to a specific stub.
     *
     * @param $name
     * @return mixed
     */
    protected function getPath($name)
    {
        return $this->getOutputPath(snake_case($name) . '.js');
    }

    /**
     * Get an empty stub.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getStub()
    {
        return $this->files->get($this->getStubPath() . "/blank_service.stub.js");
    }

    /**
     * Perform token replacement on the stub file.
     *
     * @param string $serviceName
     * @param string $thriftServiceName
     * @param string $thriftName
     * @param $thriftFile
     * @param array $serviceDefinition
     * @param $stub
     * @return mixed
     */
    protected function populateStub($serviceName, $thriftServiceName, $thriftName, $thriftFile, array $serviceDefinition, $stub)
    {
        $stub = str_replace('{{client_name}}', $serviceName, $stub);
        $stub = str_replace('{{thrift_name}}', $thriftName, $stub);
        $stub = str_replace('{{thrift_service_name}}', $thriftServiceName, $stub);
        $dependencies = $this->buildDependencies($thriftFile, $thriftName);
        $stub = str_replace('{{dependencies}}', $dependencies, $stub);
        $definition = $this->buildDefinition($serviceName, $serviceDefinition);
        $stub = str_replace('{{internal_service_def}}', $definition['internal'], $stub);
        $stub = str_replace('{{public_service_def}}', $definition['public'], $stub);

        return $stub;
    }

    protected function buildDependencies($thriftFile, $thriftName)
    {
        $basePath = rtrim($this->baseDependencyPath, '/');
        if (strlen($basePath)) {
            $basePath .= '/';
        }
        $ext = trim($this->dependencyExt, '.');
        return "'{$basePath}{$thriftFile}_types.{$ext}', '{$basePath}{$thriftName}.{$ext}'";
    }

    /**
     * Generate the text string used to populate the text file.
     *
     * @param $clientName
     * @param array $definition
     * @return string
     * @internal param array $service
     */
    protected function buildDefinition($clientName, array $definition)
    {
        $publicDef = [];
        $internalDef = [];

        foreach ($definition as $method => $params) {
            $internalName = 'callApi' . ucfirst($method);
            $args = $this->buildClientArgs($params);
            $publicDef[$method] = $internalName;
            $internalDef []= <<<EOF
            
      function $internalName({$args}) {
        var deferred = \$q.defer();
        
        var client = getClient();
        
        try {
          client.{$method}({$args}, function (result) {
            deferred.resolve(result);
          });
        } catch(ouch){
          deferred.reject(ouch);
        }

        return deferred.promise;
      }
EOF;
        }

        return ['public' => "{\r\n\t\t\t" . implode(",\r\n\t\t\t", $this->buildPublicApi($publicDef)) . "\r\n\t\t\t}", 'internal' => implode("\r\n", $internalDef)];

    }

    protected function buildPublicApi(array $methods)
    {
        $api = [];

        foreach ($methods as $public => $private) {
            $api []= "\t{$public}: {$private}";
        }

        return $api;
    }

    /**
     * Get a concatenated string of argument names for this service method.
     *
     * @param array $args
     * @return string
     */
    protected function buildClientArgs(array $args)
    {
        return implode(',', array_keys($args));
    }

}
