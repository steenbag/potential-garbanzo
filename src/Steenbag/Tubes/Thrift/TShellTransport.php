<?php namespace Steenbag\Tubes\Thrift;

use Steenbag\Tubes\Auth\HttpRequestCanonicalizer;
use Steenbag\Tubes\Auth\RequestSigner;
use Steenbag\Tubes\Certificate\Certificate;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use GuzzleHttp\Stream\Stream;
use Thrift\Exception\TTransportException;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Transport\TTransport;

/**
 * This class provides a client that we can use on NSERC, which
 * does not support 'real' curl requests. Instead, requests must
 * be emulated by shelling out to a new process over the CLI.
 *
 * Class TNsercCliClient
 * @package Steenbag\Tubes\Thrift
 */
class TShellTransport extends TTransport
{

    /**
     * The host to connect to
     *
     * @var string
     */
    protected $host_;

    /**
     * The URI to request
     *
     * @var string
     */
    protected $uri_;

    /**
     * The encoding strategy to use.
     *
     * @var string
     */
    protected $encoding_;

    /**
     * The path to the PHP executable.
     *
     * @var string
     */
    protected $phpPath_;

    /**
     * The path to the php script to execute over the shell.
     *
     * @var string
     */
    protected $cliPath_;

    /**
     * Buffer for the request data
     *
     * @var string
     */
    protected $buf_;

    /**
     * Buffer for the response data.
     *
     * @var string
     */
    protected $responseBuf_;

    /**
     * Input socket stream.
     *
     * @var resource
     */
    protected $handle_;

    /**
     * Read timeout
     *
     * @var float
     */
    protected $timeout_;

    /**
     * http headers
     *
     * @var array
     */
    protected $headers_;

    /**
     * The certificate to sign the request.
     *
     * @var Certificate
     */
    protected $certificate;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Make a new Shell client.
     *
     * @param string $host
     * @param string $uri
     * @param $php
     * @param $shell
     * @param string $encoding
     * @param Certificate $certificate
     * @param array $headers
     */
    public function __construct($host, $uri = '', $php, $shell, $encoding = 'text', Certificate $certificate = null, array $headers = []) {
        if ((TStringFuncFactory::create()->strlen($uri) > 0) && ($uri{0} != '/')) {
            $uri = '/' . $uri;
        }
        $this->host_ = $host;
        $this->uri_ = $uri;
        $this->encoding_ = $encoding;
        $this->buf_ = '';
        $this->handle_ = null;
        $this->timeout_ = null;
        $this->headers_ = $headers;
        $this->phpPath_ = $php;
        $this->cliPath_ = $shell;
        $this->certificate = $certificate;
    }

    /**
     * Set read timeout
     *
     * @param float $timeout
     */
    public function setTimeoutSecs($timeout) {
        $this->timeout_ = $timeout;
    }

    /**
     * Set the path to the PHP binary.
     *
     * @param $path
     */
    public function setPhpPath($path)
    {
        $this->phpPath_ = $path;
    }

    /**
     * Set the path to the 'web service' end point.
     *
     * @param $path
     */
    public function setCliPath($path)
    {
        $this->cliPath_ = $path;
    }

    /**
     * Whether this transport is open.
     *
     * @return boolean true if open
     */
    public function isOpen() {
        return true;
    }

    /**
     * Open the transport for reading/writing
     *
     */
    public function open() {}

    /**
     * Close the transport.
     */
    public function close() {
        if ($this->handle_) {
            @fclose($this->handle_);
            $this->handle_ = null;
        }
    }

    /**
     * Read some data into the array.
     *
     * @param int    $len How much to read
     * @return string The data that has been read
     * @throws TTransportException if cannot read any more data
     */
    public function read($len) {
        $data = $this->responseBuf_->read($len);
        if ($data === FALSE || $data === '') {
            $md = stream_get_meta_data($this->handle_);
            if ($md['timed_out']) {
                throw new TTransportException('THttpClient: timed out reading '.$len.' bytes from '.$this->host_.$this->uri_, TTransportException::TIMED_OUT);
            } else {
                throw new TTransportException('THttpClient: Could not read '.$len.' bytes from '.$this->host_.$this->uri_, TTransportException::UNKNOWN);
            }
        }
        return $data;
    }

    /**
     * Writes some data into the pending buffer
     *
     * @param string $buf  The data to write
     */
    public function write($buf) {
        $this->buf_ .= $buf;
    }

    /**
     * Opens and sends the actual request over the HTTP connection
     *
     * @throws TTransportException if a writing error occurs
     */
    public function flush() {
        $encoding = $this->encoding_;
        //Build launch command
        $launchCommandTemplate = $this->phpPath_ . ' ' . $this->cliPath_ . ' --encoding=%s --uri=%s --method=%s --headers=%s --content=%s';

        $url = $this->host_ . $this->uri_;

        $contents = $this->encodeParam($this->buf_, $encoding);
        $rawContents = base64_decode($contents);
        $this->addHeaders([
            'Compress' => true,
            'Thrift-Transport-Encoding' => $encoding,
            'Content-Length' => strlen($rawContents)
        ]);

        // Sign the request.
        if (isset($this->certificate)) {
            $signer = new RequestSigner();
            $date = date(DATE_ISO8601);

            $signature = $signer->sign($this->certificate, $this->uri_, strlen($this->buf_), md5($this->buf_), $date, $this->headers_);

            $tmpHeaders = [
                'X-Thrift-Auth' => sprintf('%s %s %s', "SharedKey", $this->certificate->getApiKey(), $signature),
                'Request-Date' => $date
            ];
            $this->addHeaders($tmpHeaders);
        }

        $headers = $this->encodeParam($this->headers_, $encoding);

        $launchCommand = sprintf($launchCommandTemplate, escapeshellarg($encoding), escapeshellarg(urldecode($url)), escapeshellarg('POST'), escapeshellarg($headers), escapeshellarg($contents), true);
        $this->handle_ = popen($launchCommand, 'r');

        // Connect failed?
        if ($this->handle_ === FALSE) {
            $this->handle_ = null;
            $error = 'TShellClient: Could not connect to ' . $this->host_ . $this->uri_;
            throw new TTransportException($error, TTransportException::NOT_OPEN);
        }

        //Read all contents from outputFile
        if ($encoding === 'binary') {
            $response = gzinflate(hex2bin(fgets($this->handle_)));
        } else {
            $response = gzinflate(base64_decode(fgets($this->handle_)));
        }

        if (!feof($this->handle_)) {
            throw new \Exception('Error processing the shell response.');
        }
        $this->responseBuf_ = new TMemoryBuffer($response);

        //Close the stream since we are done with it.
        pclose($this->handle_);
    }

    /**
     * Add one or more headers
     *
     * @param $headers
     */
    public function addHeaders($headers) {
        $this->headers_ = array_merge($this->headers_, $headers);
    }

    /**
     * Encode a parameter to the wire format.
     *
     * @param $param
     * @param string $encoding
     * @return string
     */
    protected function encodeParam($param, $encoding = 'text')
    {
        if ($encoding === 'binary') {
            switch(gettype($param)) {
                case 'string':
                    $data = bin2hex($param);
                    break;
                case 'array':
                case 'object':
                    $data = bin2hex(json_encode($param));
                    break;
            }

            return base64_encode($data);
        } else {
            return base64_encode(json_encode($param));
        }
    }

}
