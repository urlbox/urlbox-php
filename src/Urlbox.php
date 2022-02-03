<?php

namespace Urlbox\Screenshots;

use GuzzleHttp\Client;
use InvalidArgumentException;

class Urlbox
{
    /**
     * @var string
     */
    private $api_key;
    /**
     * @var string
     */
    private $api_secret;

    /**
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct( $api_key, $api_secret )
    {
        $this->ensureIsValidCredentials($api_key, $api_secret);

        $this->api_key    = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * @param string $api_key
     * @param string $api_secret
     *
     * @return Urlbox
     */
    public static function fromCredentials( $api_key, $api_secret )
    {
        return new self( $api_key, $api_secret );
    }

    /**
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function capture( $options )
    {
        $client = new Client();
        return $client->request('GET', $this->generateUrl($options));
    }

    /**
     * @param array $options
     * @param string $filename filename including path
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function save( $options, $filename )
    {
        $response = $this->capture($options);
        file_put_contents($filename, $response->getBody());
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function generateUrl( $options )
    {
        $format = isset( $options['format'] ) ? $options['format'] : 'png';
        unset( $options['format'] );
        $_parts = [];
        foreach ( $options as $key => $values ) {
            $values = is_array( $values ) ? $values : [ $values ];
            foreach ( $values as $value ) {
                if ( isset( $value ) ) {
                    $encodedValue = $this->sanitizeValue( $value );
                    $_parts[]     = "$key=$encodedValue";
                }
            }
        }
        $query_string = implode( "&", $_parts );
        $TOKEN        = hash_hmac( "sha1", $query_string, $this->api_secret );

        return "https://api.urlbox.io/v1/$this->api_key/$TOKEN/$format?$query_string";
    }

    private function sanitizeValue( $val )
    {
        $type = gettype( $val );
        if ( $type == 'string' ) {
            return $this->encodeURIComponent( $val );
        }

        return var_export( $val, true );

    }

    /**
     * @param string $val
     *
     * @return string
     */
    public function encodeURIComponent2( $val )
    {
        $result = rawurlencode( $val );
        $result = str_replace( '+', '%20', $result );
        $result = str_replace( '%21', '!', $result );
        $result = str_replace( '%2A', '*', $result );
        $result = str_replace( '%27', '\'', $result );
        $result = str_replace( '%28', '(', $result );
        $result = str_replace( '%29', ')', $result );

        return $result;
    }

    /**
     * @param string $val
     *
     * @return string
     */
    public function encodeURIComponent( $val )
    {
        $revert = array( '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' );

        return strtr( rawurlencode( $val ), $revert );
    }

    /**
     * @param string $api_key
     * @param string $api_secret
     *
     * @return void
     */
    private function ensureIsValidCredentials( $api_key, $api_secret )
    {
        if ( ! $api_key ) {
            throw new InvalidArgumentException( 'requires an api key' );
        }
        if ( ! $api_secret ) {
            throw new InvalidArgumentException( 'requires an api secret' );
        }
    }
}