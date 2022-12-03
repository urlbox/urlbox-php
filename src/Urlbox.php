<?php

namespace Urlbox\Screenshots;

use Exception;
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
    private ?string $webhook_secret;
    private Client $client;

    /**
     * @param string $api_key
     * @param string $api_secret
     * @param string|null $webhook_secret
     * @param Client|null $client
     *
     * @throws InvalidArgumentException
     */
    public function __construct( string $api_key, string $api_secret, ?string $webhook_secret = null, ?Client $client = null )
    {
        $this->ensureIsValidCredentials( $api_key, $api_secret );

        $this->api_key        = $api_key;
        $this->api_secret     = $api_secret;
        $this->webhook_secret = $webhook_secret;

        $this->client = $client ?? new Client();
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
            throw new InvalidArgumentException( 'Requires an api key - https://www.urlbox.io/dashboard/projects' );
        }

        if ( empty( $api_secret ) ) {
            throw new InvalidArgumentException( 'Requires an api secret - https://www.urlbox.io/dashboard/projects' );
        }
    }

    /**
     * @param string $api_key
     * @param string $api_secret
     * @param string|null $webhook_secret
     * @param Client|null $client
     *
     * @return Urlbox
     */
    public static function fromCredentials( string $api_key, string $api_secret, ?string $webhook_secret, ?Client $client = null ): Urlbox
    {
        return new self( $api_key, $api_secret, $webhook_secret, $client );
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
                    'Authorization' => 'Bearer ' . $this->api_secret,
                ],
                RequestOptions::JSON    => $options
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function verifyWebhookSignature( string $header, string $content ): bool
    {
        if ( empty( $this->webhook_secret ) ) {
            throw new Exception( 'Unable to verify signature as Webhook Secret is not set. You can find your webhook secret inside your project\'s settings - https://www.urlbox.io/dashboard/projects' );
        }

        if ( empty( $header ) ) {
            throw new InvalidArgumentException( 'Unable to verify signature as header is empty. Please ensure you pass the `x-urlbox-signature` from the header of the webhook response' );
        }

        $generated_hash = hash_hmac( 'sha256', $this->getTimestamp( $header ) . '.' . $content, $this->webhook_secret );
        $request_hash   = $this->getSignature( $header );

        return $generated_hash === $request_hash;
    }

    private function getTimestamp( $header )
    {
        return array_reverse( explode( 't=', explode( ',', $header )[0], 2 ) )[0];
    }

    private function getSignature( $header ): string
    {
        return array_reverse( explode( 'sha256=', $header, 2 ) )[0];
    }
}