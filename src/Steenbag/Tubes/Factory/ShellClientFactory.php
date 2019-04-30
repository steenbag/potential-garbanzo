<?php namespace Steenbag\Tubes\Factory;

use Steenbag\Tubes\Contract\Repository;
use Steenbag\Tubes\Contract\ThriftClientFactory as Contract;
use Steenbag\Tubes\Exception\UnsupportedClientException;
use Steenbag\Tubes\Thrift\TShellTransport;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Protocol\TJSONProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use \Steenbag\Tubes\Factory\ThriftClientFactory as BaseFactory;

class ShellClientFactory extends BaseFactory implements Contract
{
    protected $protocol;

    protected $pathToPhp;

    protected $scriptTarget;

    public function __construct(Repository $config)
    {
        $this->protocol = $config->get('protocol', 'binary');
        $this->pathToPhp = $config->get('path-to-php', PHP_BINARY);
        $this->scriptTarget = $config->get('script-target', null);
    }

    /**
     * @param $type
     * @param $endpoint
     * @param null $protocol
     * @param null $certificate
     * @param bool $multiplexed
     * @return mixed|void
     * @throws UnsupportedClientException
     */
    public function createClient($type, $endpoint, $protocol = null, $certificate = null, $multiplexed = true)
    {
        $typeDef = $this->getType($type);
        $namespace = isset($typeDef['namespace']) ? $typeDef['namespace'] : 'Steenbag\Tubes\General';
        $serviceName = isset($typeDef['service-name']) ? $typeDef['service-name'] : $type;
        $clientClass = isset($typeDef['client-class']) ? $typeDef['client-class'] : null;

        if (is_null($clientClass)) {
            $storeClass = ucfirst($type);

            $clientClass = '\\' . $namespace . '\\' . $storeClass . '\\' . $storeClass . 'Client';
        }

        $parts = parse_url($endpoint);
        if (strpos($parts['path'], '/index.php') !== false) {
            $pathParts = explode('/index.php', $parts['path']);
            $parts['path'] = array_pop($pathParts);
            $parts['path'] = ltrim($parts['path'], '/');
        }

        // Create our low-level transport.
        $transport = new TShellTransport($parts['scheme'] . '://' . $parts['host'], $parts['path'], $this->pathToPhp, $this->scriptTarget, $protocol ?: $this->protocol, $certificate);
        if ($multiplexed) {
            $transport->addHeaders(['X-Thrift-Multiplexed' => true]);
        }

        // Create the protocol.
        $protocol = $this->getThriftProtocol($protocol ?: $this->protocol, $transport);

        if ($multiplexed) {
            $multiProtocol = new TMultiplexedProtocol($protocol, $serviceName);
        }

        return new $clientClass($multiplexed ? $multiProtocol : $protocol);
    }

    /**
     * Return the requested Thrift Protocol.
     *
     * @param $protocol
     * @param $buffer
     * @return TProtocol
     */
    public function getThriftProtocol($protocol, $buffer)
    {
        switch ($protocol) {
            case 'json':
            case 'text':
                return new TJSONProtocol($buffer);
            case 'compact':
                return new TCompactProtocol($buffer);
            case 'binary':
                return new TBinaryProtocol($buffer);
        }

        throw new \InvalidArgumentException("The {$protocol} protocol is not supported at this time.");
    }

}
