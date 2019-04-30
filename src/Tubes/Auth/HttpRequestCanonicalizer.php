<?php namespace Steenbag\Tubes\Auth;

class HttpRequestCanonicalizer
{

    protected static $signingHeaders = [
        'Authorization'
    ];

    /**
     * Build the string that we use to sign this request.
     *
     * @param $uri
     * @param $contentLength
     * @param $contentHash
     * @param $date
     * @param array $headers
     * @return string
     */
    public static function canonicalizeRequest($uri, $contentLength, $contentHash, $date, array $headers)
    {
        $uri = str_replace('index.php', '', $uri);
        $uri = ltrim(trim($uri, '/ '), '/ ');

        $headers = json_encode(self::getHeaders($headers));

        return implode('-', array_merge([$uri, $contentLength, $contentHash, $date]));
    }

    /**
     * Return only the headers used for signing requests.
     *
     * @param array $headers
     */
    protected static function getHeaders(array $headers)
    {
        $_headers = [];

        foreach ($headers as $k => $v) {
            if (in_array($k, self::$signingHeaders)) {
                $_headers[$k] = $v;
            }
        }

        return self::sortArray($_headers);
    }

    /**
     * Recursively sort an array by keys.
     *
     * @param $array
     */
    protected static function sortArray(&$array)
    {
        if (self::isAssoc($array)) {
            ksort($array);
        } else {
            asort($array);
        }
        foreach ($array as &$subArray) {
            if (is_array($subArray)) {
                self::sortArray($subArray);
            }
        }
    }

    /**
     * Return true if an array is associative.
     *
     * @param array $arr
     * @return bool
     */
    protected static function isAssoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
