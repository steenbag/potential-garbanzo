<?php namespace Steenbag\Tubes\Auth;

use Steenbag\Tubes\Certificate\Certificate;

class RequestSigner
{

    /**
     * Generate the request signature.
     *
     * @param Certificate $certificate
     * @param $uri
     * @param $contentLength
     * @param $contentHash
     * @param $date
     * @param array $headers
     * @return string
     */
    public function sign(Certificate $certificate, $uri, $contentLength, $contentHash, $date, array $headers = [])
    {
        $requestData = HttpRequestCanonicalizer::canonicalizeRequest($uri, $contentLength, $contentHash, $date, $headers);

        return $certificate->sign($requestData);
    }

    /**
     * Verify a signature.
     *
     * @param Certificate $certificate
     * @param $signature
     * @param $uri
     * @param $contentLength
     * @param $contentHash
     * @param $date
     * @param array $headers
     * @return boolean
     */
    public function verify(Certificate $certificate, $signature, $uri, $contentLength, $contentHash, $date, array $headers)
    {
        $requestData = HttpRequestCanonicalizer::canonicalizeRequest($uri, $contentLength, $contentHash, $date, $headers);

        return $certificate->verifySignature($requestData, $signature);
    }

}
