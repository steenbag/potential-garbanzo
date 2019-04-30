<?php namespace Steenbag\Tubes\Certificate;

use Steenbag\Tubes\Contract\CertStore;

class Certificate
{

    protected $certStore;

    protected $properties = [];

    protected $apiKey;

    protected $publicKey;

    protected $privateKey;

    protected $password;

    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($key)
    {
        $this->apiKey = $key;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function setPublicKey($key)
    {
        $this->publicKey = $key;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function setPrivateKey($key)
    {
        $this->privateKey = $key;
    }

    public function setCertStore(CertStore $certStore)
    {
        $this->certStore = $certStore;
    }

    public function sign($plaintext)
    {
        if ($this->certStore) {
            return $this->certStore->sign($this, $plaintext, $this->password);
        }
    }

    public function verifySignature($plaintext, $signature)
    {
        if ($this->certStore) {
            return $this->certStore->verifySignature($this, $plaintext, $signature);
        }
    }

    public function encrypt($plaintext)
    {
        if ($this->certStore) {
            return $this->certStore->encrypt($this, $plaintext);
        }
    }

    public function decrypt($cipherText)
    {
        if ($this->certStore) {
            return $this->certStore->decrypt($this, $cipherText, $this->password);
        }
    }

}
