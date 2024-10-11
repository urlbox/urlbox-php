<?php

namespace Urlbox\Screenshots;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;

class Urlbox
{
    private string $baseUrl = 'https://api.urlbox.com/v1';
    private string $apiKey;
    private string $apiSecret;
    private ?string $webhookSecret;
    private Client $client;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param string|null $webhookSecret
     * @param Client|null $client
     *
     * @throws InvalidArgumentException
     */
    public function __construct( string $apiKey, string $apiSecret, ?string $webhookSecret = null, ?Client $client = null )
    {
        $this->ensureIsValidCredentials( $apiKey, $apiSecret );

        $this->apiKey        = $apiKey;
        $this->apiSecret     = $apiSecret;
        $this->webhookSecret = $webhookSecret;

        $this->client = $client ?? new Client();
    }

    /**
     * Ensure the user has passed an API key and secret.
     *
     * @param string $apiKey
     * @param string $apiSecret
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function ensureIsValidCredentials( string $apiKey, string $apiSecret )
    {
        if ( empty( $apiKey ) ) {
            throw new InvalidArgumentException( 'Requires an api key - https://www.urlbox.com/dashboard/projects' );
        }

        if ( empty( $apiSecret ) ) {
            throw new InvalidArgumentException( 'Requires an api secret - https://www.urlbox.com/dashboard/projects' );
        }
    }

    /**
     * Returns a new instance of Urlbox
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @param string|null $webhookSecret
     * @param Client|null $client
     *
     * @return Urlbox
     * @throws InvalidArgumentException
     */
    public static function fromCredentials( string $apiKey, string $apiSecret, ?string $webhookSecret = null, ?Client $client = null ): Urlbox
    {
        return new self( $apiKey, $apiSecret, $webhookSecret, $client );
    }

    /**
     * Calls the Urlbox /sync endpoint
     * @param array $options
     * @param string|null $saveToDiskPath - A path to save the image to
     *
     * @return array{renderUrl: string, size: int, localPath: string}
     * @throws GuzzleException
     */
    public function render( array $options, ?string $saveToDiskPath = null ): array
    {
        $response = $this->makeUrlboxPostRequest( '/render/sync', $options );

        if ( $saveToDiskPath !== null ) {
            $imageResponse = $this->client->get( $response['renderUrl'] );
            file_put_contents( $saveToDiskPath, $imageResponse->getBody()->getContents() );
            $response['localPath'] = $saveToDiskPath;
        }

        return $response;
    }

    /**
     * Make a POST request to Urlbox
     * @param string $endpoint - The endpoint EG /sync
     * @param array $options - The render options
     *
     * @return array{renderUrl: string, size: int}
     * @throws GuzzleException
     */
    private function makeUrlboxPostRequest( string $endpoint, array $options ): array
    {
        $response = $this->client->post(
            $this->baseUrl . $endpoint,
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $this->apiSecret,
                ],
                RequestOptions::JSON    => $options
            ]
        );

        return json_decode( $response->getBody()->getContents(), true );
    }

    /**
     * @param array $options
     *
     * @return array{status: string, renderId: string, statusUrl: string}
     * @throws GuzzleException
     */
    public function renderAsync( array $options ): array
    {
        return $this->makeUrlboxPostRequest( '/render', $options );
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function generateSignedUrl( array $options ): string
    {
        return $this->generateUrl( $options, true );
    }

    /**
     * @param array $options
     * @param bool $signed
     *
     * @return string
     */
    private function generateUrl( array $options, bool $signed = true ): string
    {
        $format = $options['format'] ?? 'png';
        unset( $options['format'] );

        $queryStringParts = [];
        foreach ( $options as $key => $values ) {
            $values = is_array( $values ) ? $values : [ $values ];
            foreach ( $values as $value ) {
                if ( isset( $value ) ) {
                    $encodedValue       = $this->sanitizeValue( $value );
                    $queryStringParts[] = "$key=$encodedValue";
                }
            }
        }

        $queryString = implode( "&", $queryStringParts );

        $generatedUrl = $this->baseUrl . '/' . $this->apiKey . '/';

        if ( $signed ) {
            $generatedUrl .= hash_hmac( "sha1", $queryString, $this->apiSecret ) . '/';
        }

        return $generatedUrl . $format . '?' . $queryString;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public function generateUnsignedUrl( array $options ): string
    {
        return $this->generateUrl( $options, false );
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
     * Verifies the signature of an incoming webhook request to ensure its authenticity.
     * @param string $header
     * @param string $content
     *
     * @return bool
     * @throws Exception
     */
    public function verifyWebhookSignature( string $header, string $content ): bool
    {
        if ( empty( $this->webhookSecret ) ) {
            throw new Exception( 'Unable to verify signature as Webhook Secret is not set. You can find your webhook secret inside your project\'s settings - https://www.urlbox.com/dashboard/projects' );
        }

        if ( empty( $header ) ) {
            throw new InvalidArgumentException( 'Unable to verify signature as header is empty. Please ensure you pass the `x-urlbox-signature` from the header of the webhook response' );
        }

        $generatedHash = hash_hmac( 'sha256', $this->getTimestamp( $header ) . '.' . $content, $this->webhookSecret );
        $requestHash   = $this->getSignature( $header );

        return $generatedHash === $requestHash;
    }

    /**
     * @param string $header
     *
     * @return string
     */
    private function getTimestamp( string $header ): string
    {
        return array_reverse( explode( 't=', explode( ',', $header )[0], 2 ) )[0];
    }

    /**
     * @param string $header
     *
     * @return string
     */
    private function getSignature( string $header ): string
    {
        return array_reverse( explode( 'sha256=', $header, 2 ) )[0];
    }

}
