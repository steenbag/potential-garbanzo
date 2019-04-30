<?php namespace Steenbag\Tubes\Console;

use Steenbag\Tubes\Generator\NgClientGenerator;
use Steenbag\Tubes\Generator\NgServiceGenerator;
use Steenbag\Tubes\Generator\ThriftParser;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class NgThriftServiceCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'tubes:thrift-gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the Angular services for the movies app based on the passed-in files.';


    protected $serviceGenerator;

    public function __construct(NgServiceGenerator $serviceCreator)
    {
        parent::__construct();
        $this->serviceGenerator = $serviceCreator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function fire()
    {
        $service = $this->argument('service');
        $name = $this->inferServiceName();
        $thriftFile = $this->argument('thrift');

        $service = $this->getParsedService($service);
        $this->info("Generating {$name}");
        $this->serviceGenerator->create($name, $service['name'], $service['thrift_name'], $thriftFile, $service['methods'], dash_case($name));
    }

    /**
     * @param $service
     * @return array
     * @throws \ReflectionException
     */
    protected function getParsedService($service)
    {
        if (strpos($service, ':')) {
            $parts = explode(':', $service);
            $thriftClass = $parts[1];
            $class = str_replace('.', '\\', $thriftClass);
        } else {
            $class = str_replace('.', '\\', $service);
        }
        if (! is_a($class, "Steenbag\Tubes\WebService\BaseService", true)) {
            throw new \InvalidArgumentException("{$class} is not a Tubes Service.");
        }
        $thriftName = $class::getThriftName();
        $clientClass = $thriftName . 'Client';
        $parser = new ThriftParser($clientClass);
        $thriftServiceName = $class::getThriftServiceName();
        $service = $parser->getThriftService();
        return ['methods' => $service, 'name' => $thriftServiceName, 'thrift_name' => class_basename($thriftName)];
    }

    /**
     * @return mixed
     */
    protected function inferServiceName()
    {
        $argName = $this->argument('name');
        if ($argName) {
            return $argName;
        }
        $service = $this->argument('service');
        $class = str_replace('.', '\\', $service);
        $serviceName = class_basename($class);

        return $serviceName;
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['service', InputArgument::REQUIRED, 'Class to generate services from. Must be the server-side class.'],
            ['name', InputArgument::REQUIRED, 'The name of the Angular Service to generate.'],
            ['thrift', InputArgument::REQUIRED, 'The name of the thrift file this service is based off.']
        ];
    }

}
