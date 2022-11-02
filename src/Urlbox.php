<?php

namespace Urlbox\Screenshots;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class Urlbox
{
    private string $base_url = 'https://api.urlbox.io/v1';
    private string $api_key;
    private string $api_secret;
    private Client $client;

    /**
     * @param string $api_key
     * @param string $api_secret
     * @param Client $client
     *
     * @throws InvalidArgumentException
     */
    public function __construct( string $api_key, string $api_secret, Client $client )
    {
        $this->ensureIsValidCredentials( $api_key, $api_secret );

        $this->api_key    = $api_key;
        $this->api_secret = $api_secret;
        $this->client     = $client;
    }

    /**
     * @param string $api_key
     * @param string $api_secret
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function ensureIsValidCredentials( string $api_key, string $api_secret )
    {
        if ( empty( $api_key ) ) {
            throw new InvalidArgumentException( 'requires an api key' );
        }

        if ( empty( $api_secret ) ) {
            throw new InvalidArgumentException( 'requires an api secret' );
        }
    }

    /**
     * @param string $api_key
     * @param string $api_secret
     * @param Client $client
     *
     * @return Urlbox
     */
    public static function fromCredentials( string $api_key, string $api_secret, Client $client ): Urlbox
    {
        return new self( $api_key, $api_secret, $client );
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function generateUrl( array $options ): string
    {
        $format = $options['format'] ?? 'png';
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
        $token        = hash_hmac( "sha1", $query_string, $this->api_secret );

        return $this->base_url . '/' . $this->api_key . '/' . $token . '/' . $format . '?' . $query_string;
    }

    /**
     * @param mixed $value
     *
     * @return string|null
     */
    private function sanitizeValue( $value ): ?string
    {
        if ( is_string( $value ) ) {
            return $this->encodeURIComponent( $value );
        }

        return var_export( $value, true );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function encodeURIComponent( string $value ): string
    {
        $revert = array( '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' );

        return strtr( rawurlencode( $value ), $revert );
    }

    /**
     * @param array $options
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function capture( array $options ): ResponseInterface
    {
        return $this->client->request( 'GET', $this->generateUrl( $options ) );
    }

    /**
     * @param array $options
     * @param string $filename filename including path
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function save( array $options, string $filename ): bool
    {
        $response = $this->capture( $options );
        file_put_contents( $filename, (string) $response->getBody() );

        return true;
    }

    /**
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function webhook( array $options ): ResponseInterface
    {
        if ( ! array_key_exists( 'webhook_url', $options ) ) {
            throw new InvalidArgumentException(
                'You must include "webhook_url" in the options - https://www.urlbox.io/docs/webhooks'
            );
        }

        return $this->client->post(
            $this->base_url . '/render',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'bearer ' . $this->api_secret,
                ],
                RequestOptions::JSON    => [ $options ]
            ]
        );
    }
}