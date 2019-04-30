<?php namespace Steenbag\Tubes\Certificate;

use phpseclib\Crypt\RSA;
use Steenbag\Tubes\Contract;
use Steenbag\Tubes\Contract\ApiKey;
use Steenbag\Tubes\Exception\MissingCertificateException;
use ZipArchive;

class RsaZipCertStore implements Contract\CertStore
{

    protected $basePath;

    protected $filesystem;

    /** @var \Closure */
    protected static $passwordDecrypter;

    public function __construct(Contract\FileSystem $filesystem = null)
    {
        $this->filesystem = $filesystem;
        self::setPasswordDecrypter(function($password) {
            return $password;
        });
    }

    /**
     * Provide a closure to decrypt the password of the API key.
     *
     * @param \Closure $decrypter
     */
    public static function setPasswordDecrypter(\Closure $decrypter)
    {
        self::$passwordDecrypter = $decrypter;
    }

    public function setBasePath($path)
    {
        $this->basePath = $path;
    }

    /**
     * Retrieve the given certificate based on the API key identifier.
     *
     * @param ApiKey $apiKey
     * @return Certificate
     * @throws MissingCertificateException
     */
    public function getCertificate(ApiKey $apiKey)
    {
        $key = $apiKey->api_key;
        $path = $this->pathToCert($key);
        // Retrieve the container.
        $container = $this->getCertContainer($path);
        // Get the keys from the container.
        $publicKey = $container->getFromName('id_rsa.pub');
        $privateKey = $container->getFromName('id_rsa');
        // Instantiate the certificate, and assign the properties.
        $certificate = new Certificate;
        $certificate->setCertStore($this);
        $certificate->setApiKey($apiKey->api_key);
        $certificate->setPublicKey($publicKey);
        $certificate->setPrivateKey($privateKey);
        $certificate->setPassword($apiKey->getPassword());

        return $certificate;
    }

    /**
     * Create a new certificate.
     *
     * @param ApiKey $apiKey
     * @param array $properties
     * @return bool
     */
    public function createCert(ApiKey $apiKey, array $properties = [])
    {
        $password = $apiKey->password;
        $certificate = new Certificate;

        $rsa = $this->createCrypto($password);

        if (isset($properties['public_key_format'])) {
            $rsa->setPublicKeyFormat($properties['public_key_format']);
        }

        if (isset($properties['private_key_format'])) {
            $rsa->setPublicKeyFormat($properties['private_key_format']);
        }

        if (isset($properties['rsa_exponent'])) {
            define('CRYPT_RSA_EXPONENT', $properties['rsa_exponent']);
        }

        if (isset($properties['smallest_prime'])) {
            define('CRYPT_RSA_SMALLEST_PRIME', $properties['smallest_prime']);
        }

        $keySize = isset($properties['keysize']) ? $properties['keysize'] : 1024;

        extract($rsa->createKey($keySize));

        $certificate->setPublicKey($publickey);
        $certificate->setPrivateKey($privatekey);

        return $this->writeCert($apiKey->api_key, $certificate, $password);
    }

    /**
     * Delete the certificate associated with the given key.
     *
     * @param string $apiKey
     * @return bool
     * @throws \Exception
     */
    public function deleteCert($apiKey)
    {
        $path = $this->pathToCert($apiKey);

        return $this->filesystem->delete($path);
    }

    protected function writeCert($apiKey, Certificate $certificate, $password)
    {
        $path = $this->pathToCert($apiKey);

        $container = $this->getCertContainer($path, true);

        $container->addFromString('id_rsa', $certificate->getPrivateKey());
        $container->addFromString('id_rsa.pub', $certificate->getPublicKey());

        return $container->close();
    }

    /**
     * Return a zip container to store our certificates.
     *
     * @param $filename
     * @param bool $create
     * @return ZipArchive
     * @throws MissingCertificateException
     */
    protected function getCertContainer($filename, $create = false)
    {
        $zip = new ZipArchive();
        if ($zip->open($filename, $create ? ZipArchive::CREATE : null) !== true) {
            throw new MissingCertificateException("Cannot open certificate " . basename($filename) . ".");
        }

        return $zip;
    }

    /**
     * Return the disk path to the requested API key.
     *
     * @param $apiKey
     * @return string
     * @throws \Exception
     */
    protected function pathToCert($apiKey)
    {
        if (! $this->basePath) {
            throw new \Exception("Invalid Tubes Configuration. The Base Path must be set on the Cert Store.");
        }

        return rtrim($this->basePath, '/') . '/' . $this->sanitizeFileName($apiKey) . '.api';
    }

    /**
     * Sanitize a filename so that it is safe to store.
     *
     * @param $string
     * @return bool|mixed|string
     */
    protected function sanitizeFileName($string) {
        $strip = ["~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?"];
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = preg_replace("/[^a-zA-Z0-9]/", "", $clean);

        return $clean;
    }

    /**
     * Sign a request.
     *
     * @param Certificate $certificate
     * @param $plaintext
     * @param $password
     * @return string
     */
    public function sign(Certificate $certificate, $plaintext, $password = null)
    {
        $rsa = $this->createCrypto($password);
        $rsa->loadKey($certificate->getPrivateKey());
        $rsa->setSignatureMode(RSA::SIGNATURE_PSS);

        return base64_encode($rsa->sign($plaintext));
    }

    /**
     * Verify the authenticity of a signature.
     *
     * @param Certificate $certificate
     * @param $plaintext
     * @param $signature
     * @return mixed
     */
    public function verifySignature(Certificate $certificate, $plaintext, $signature)
    {
        $rsa = $this->createCrypto();
        $rsa->loadKey($certificate->getPublicKey());
        $rsa->setSignatureMode(RSA::SIGNATURE_PSS);

        return $rsa->verify($plaintext, base64_decode($signature));
    }

    /**
     * Encrypt a request.
     *
     * @param Certificate $certificate
     * @param $plaintext
     * @return string
     */
    public function encrypt(Certificate $certificate, $plaintext)
    {
        $rsa = $this->createCrypto();
        $rsa->loadKey($certificate->getPublicKey());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_OAEP);

        return $rsa->encrypt($plaintext);
    }

    /**
     * Decrypt a request.
     *
     * @param Certificate $certificate
     * @param $cipherText
     * @param $password
     * @return string
     */
    public function decrypt(Certificate $certificate, $cipherText, $password = null)
    {
        $rsa = $this->createCrypto($password);
        $rsa->loadKey($certificate->getPrivateKey());
        $rsa->setEncryptionMode(RSA::ENCRYPTION_OAEP);

        return $rsa->decrypt($cipherText);
    }

    /**
     * Create the RSA class for use.
     *
     * @param $password
     * @return RSA
     */
    protected function createCrypto($password = null)
    {
        $rsa = new RSA();
        if ($password) {
            $rsa->setPassword($password);
        }

        return $rsa;
    }

    /**
     * Decrypt our password.
     *
     * @param string $password
     * @return string
     */
    protected function decryptPassword($password)
    {
        return static::$passwordDecrypter($password);
    }

}
