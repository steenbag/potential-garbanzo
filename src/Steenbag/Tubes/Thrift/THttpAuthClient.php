<?php namespace Steenbag\Tubes\Thrift;

use Steenbag\Tubes\Auth\RequestSigner;
use Steenbag\Tubes\Certificate\Certificate;
use Thrift\Exception\TTransportException;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Transport\THttpClient;

class THttpAuthClient extends THttpClient
{

    /**
     * @var Certificate
     */
    protected $certificate;

    /**
     * @var RequestSigner
     */
    protected $signer;

    /**
     * @var string
     */
    protected $encoding = 'text';

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @return RequestSigner
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * @param RequestSigner $signer
     */
    public function setSigner($signer)
    {
        $this->signer = $signer;
    }

    /**
     * @return Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @param Certificate $certificate
     */
    public function setCertificate(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Opens and sends the actual request over the connection.
     *
     * Additionally adds in all of our header stuff for authorization.
     *
     * @throws TTransportException if a writing error occurs
     */
    public function flush()
    {
        // Sign the request.
        $this->addHeaders(['Thrift-Transport-Encoding' => $this->encoding]);
        if (isset($this->certificate) && isset($this->signer)) {
            $date = date(DATE_ISO8601);

            var_dump([$this->uri_, strlen($this->buf_), md5($this->buf_), $date]);

            $signature = $this->signer->sign($this->certificate, $this->uri_, strlen($this->buf_), md5($this->buf_), $date);

            $tmpHeaders = [
                'X-Thrift-Auth' => sprintf('%s %s %s', "SharedKey", $this->certificate->getApiKey(), $signature),
                'Request-Date' => $date
            ];
            $this->addHeaders($tmpHeaders);
        }

        return parent::flush();
    }

}
