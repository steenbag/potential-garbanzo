<?php namespace Steenbag\Tubes\Contract;

use Steenbag\Tubes\Certificate\Certificate;

/**
 * The CertStore encapsulates reading and writing from certificate files.
 * @package Steenbag\Tubes\Contract
 */
interface CertStore
{

    /**
     * Retrieve the given certificate.
     *
     * @param ApiKey $apiKey
     */
    public function getCertificate(ApiKey $apiKey);

    /**
     * Create a new certificate.
     *
     * @param $apiKey
     * @param array $properties
     * @return bool
     */
    public function createCert(ApiKey $apiKey, array $properties = []);

    /**
     * Delete the certificate associated with the given key.
     *
     * @param string $apiKey
     * @return bool
     */
    public function deleteCert($apiKey);

    /**
     * Sign a request.
     *
     * @param Certificate $certificate
     * @param $plaintext
     * @param $password
     * @return string
     */
    public function sign(Certificate $certificate, $plaintext, $password = null);

    /**
     * Verify the authenticity of a signature.
     *
     * @param Certificate $certificate
     * @param $plaintext
     * @param $signature
     * @return mixed
     */
    public function verifySignature(Certificate $certificate, $plaintext, $signature);

    /**
     * Encrypt a request.
     *
     * @param Certificate $certificate
     * @param $plaintext
     * @return string
     */
    public function encrypt(Certificate $certificate, $plaintext);

    /**
     * Decrypt a request.
     *
     * @param Certificate $certificate
     * @param $cipherText
     * @param $password
     * @return string
     */
    public function decrypt(Certificate $certificate, $cipherText, $password = null);

}
