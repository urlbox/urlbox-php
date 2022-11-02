<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Urlbox\Screenshots\Urlbox;

/**
 * @covers \Urlbox\Screenshots\Urlbox
 */
final class UrlboxTest extends TestCase
{
    public function testConstructorReturnsUrlboxInstanceWhenValidCredentialsSupplied()
    {
        $this->assertInstanceOf(
            Urlbox::class,
            Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) )
        );
    }

    public function testConstructorThrowsInvalidArgumentExceptionWhenApiKeyIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        new Urlbox( '', 'API_SECRET', Mockery::mock( Client::class ) );
    }

    public function testConstructorThrowsInvalidArgumentExceptionWhenApiSecretIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        new Urlbox( 'API_KEY', '', Mockery::mock( Client::class ) );
    }

    public function testFromCredentialsReturnsUrlboxInstanceWhenValidCredentialsSupplied()
    {
        $this->assertInstanceOf(
            Urlbox::class,
            Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) )
        );
    }

    public function testFromCredentialsThrowsInvalidArgumentExceptionWhenApiKeyIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        Urlbox::fromCredentials( '', 'API_SECRET', Mockery::mock( Client::class ) );
    }

    public function testFromCredentialsThrowsInvalidArgumentExceptionWhenApiSecretIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        Urlbox::fromCredentials( 'API_KEY', '', Mockery::mock( Client::class ) );
    }

    public function testGenerateUrlDefaultFormatToPng()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );
        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
            $urlbox->generateUrl( [ 'url' => 'https://example.com' ] )
        );
    }

    public function testGenerateUrlCanSetFormatToPng()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToJpg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'jpg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/jpg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToJpeg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'jpeg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/jpeg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToAvif()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'avif'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/avif?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToWebp()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'webp'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/webp?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToPdf()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'pdf'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/pdf?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToSvg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'svg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/svg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlCanSetFormatToHtml()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'    => 'https://example.com',
            'format' => 'html'
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/html?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUrlEncodesOptionsCorrectly()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUrl( [
            'url'       => 'https://example.com/~!@#$%^&*(){}[]=:/,;?+\'"\\',
            'format'    => 'png',
            'block_ads' => true
        ] );

        $this->assertEquals(
            "https://api.urlbox.io/v1/API_KEY/897c2361c52a5eb41b9128a2b7e70ffd5fefd662/png?url=https%3A%2F%2Fexample.com%2F~!%40%23%24%25%5E%26*()%7B%7D%5B%5D%3D%3A%2F%2C%3B%3F%2B'%22%5C&block_ads=true",
            $url
        );
    }

    public function testGenerateUrlProducesCorrectUrlForTheKitchenSink()
    {
        $urlbox  = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );
        $options = [
            'format'        => 'png',
            'url'           => 'https://app_staging.example.com/misc/template_preview.php?dsfdsfsdf&acc=79&cb=ba86b4c1&regions=%5B%7B%22id%22%3A%22dsfds%22%2C%22data%22%3A%7B%22html%22%3A%22It%20works!%22%7D%2C%22type%22%3A%22html%22%7D%5D&state=published&tid=7&sig=a642316f7e0ac9d783c30ef30a89bed3204252000319a2789851bc3de65ea216',
            'delay'         => 5000,
            'selector'      => '#trynow',
            'full_page'     => true,
            'width'         => 1280,
            'height'        => '1024',
            'cookie'        => [ 'ckplns=1', 'foo=bar' ],
            'user_agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_0 like Mac OS X) AppleWebKit/602.1.32 (KHTML, like Gecko) Version/10.0 Mobile/14A5261v Safari/602.1',
            'retina'        => 'true',
            'thumb_width'   => '400',
            'crop_width'    => 500,
            'ttl'           => '604800',
            'force'         => true,
            'headless'      => false,
            'wait_for'      => '.someel',
            'click'         => '#tab-specs-trigger',
            'hover'         => 'a[href="https://google.com"]',
            'bg_color'      => '#bbbddd',
            'highlight'     => 'trump|inauguration',
            'highlightbg'   => '#11cc77',
            'highlightfg'   => 'green',
            'hide_selector' => '.modal-backdrop, #email-roadblock-topographic-modal',
            'flash'         => 'true',
            'timeout'       => 40000,
            's3_path'       => '/path/to/image with space',
            'use_s3'        => 'true',
        ];

        $url = $urlbox->generateUrl( $options );

        $this->assertEquals(
            "https://api.urlbox.io/v1/API_KEY/5280bc0f0fa198eb6fcde9fd3f32280dec496ee3/png?url=https%3A%2F%2Fapp_staging.example.com%2Fmisc%2Ftemplate_preview.php%3Fdsfdsfsdf%26acc%3D79%26cb%3Dba86b4c1%26regions%3D%255B%257B%2522id%2522%253A%2522dsfds%2522%252C%2522data%2522%253A%257B%2522html%2522%253A%2522It%2520works!%2522%257D%252C%2522type%2522%253A%2522html%2522%257D%255D%26state%3Dpublished%26tid%3D7%26sig%3Da642316f7e0ac9d783c30ef30a89bed3204252000319a2789851bc3de65ea216&delay=5000&selector=%23trynow&full_page=true&width=1280&height=1024&cookie=ckplns%3D1&cookie=foo%3Dbar&user_agent=Mozilla%2F5.0%20(iPhone%3B%20CPU%20iPhone%20OS%2010_0%20like%20Mac%20OS%20X)%20AppleWebKit%2F602.1.32%20(KHTML%2C%20like%20Gecko)%20Version%2F10.0%20Mobile%2F14A5261v%20Safari%2F602.1&retina=true&thumb_width=400&crop_width=500&ttl=604800&force=true&headless=false&wait_for=.someel&click=%23tab-specs-trigger&hover=a%5Bhref%3D%22https%3A%2F%2Fgoogle.com%22%5D&bg_color=%23bbbddd&highlight=trump%7Cinauguration&highlightbg=%2311cc77&highlightfg=green&hide_selector=.modal-backdrop%2C%20%23email-roadblock-topographic-modal&flash=true&timeout=40000&s3_path=%2Fpath%2Fto%2Fimage%20with%20space&use_s3=true",
            $url
        );
    }

    public function testCaptureReturnsApiResponse()
    {
        $responseMock = Mockery::mock( ResponseInterface::class );
        $guzzleMock   = Mockery::mock( Client::class )
                               ->shouldReceive( 'request' )
                               ->with(
                                   Mockery::capture( $method ),
                                   Mockery::capture( $url ),
                               )
                               ->andReturn( $responseMock )
                               ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );

        $response = $urlbox->capture( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ] );

        $this->assertEquals( 'GET', $method );
        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
            $url
        );
        $this->assertEquals( $responseMock, $response );
    }

    public function testCaptureThrowsException()
    {
        $exception  = Mockery::mock( ConnectException::class );
        $guzzleMock = Mockery::mock( Client::class )
                             ->shouldReceive( 'request' )
                             ->with(
                                 Mockery::capture( $method ),
                                 Mockery::capture( $url ),
                             )
                             ->andThrow( $exception )
                             ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );


        try {
            $response = $urlbox->capture( [
                'url'    => 'https://example.com',
                'format' => 'png'
            ] );

            $this->fail( 'GuzzleException not thrown' );

        } catch ( GuzzleException $e ) {
            $this->assertEquals( 'GET', $method );
            $this->assertEquals(
                'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
                $url
            );
            $this->assertEquals( $exception, $e );
        }
    }

    public function testSaveReturnsTrueAndSavesTheResponse()
    {
        $responseMock = Mockery::mock( ResponseInterface::class )
                               ->shouldReceive( 'getBody' )
                               ->andReturn( 'foobar' )
                               ->getMock();
        $guzzleMock   = Mockery::mock( Client::class )
                               ->shouldReceive( 'request' )
                               ->with(
                                   Mockery::capture( $method ),
                                   Mockery::capture( $url ),
                               )
                               ->andReturn( $responseMock )
                               ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );

        $filename = tempnam( '/tmp', 'URLBOX' );

        $result = $urlbox->save( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ], $filename );

        $this->assertEquals( 'GET', $method );
        $this->assertEquals(
            'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
            $url
        );
        $this->assertEquals( true, $result );

        $this->assertEquals(
            'foobar',
            file_get_contents( $filename )
        );
    }

    public function testSaveReturnsFalseWhenInvalidFilename()
    {
        $responseMock = Mockery::mock( ResponseInterface::class )
                               ->shouldReceive( 'getBody' )
                               ->andReturn( 'foobar' )
                               ->getMock();
        $guzzleMock   = Mockery::mock( Client::class )
                               ->shouldReceive( 'request' )
                               ->with(
                                   Mockery::capture( $method ),
                                   Mockery::capture( $url ),
                               )
                               ->andReturn( $responseMock )
                               ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );

        try {
            $urlbox->save( [
                'url'    => 'https://example.com',
                'format' => 'png'
            ], '' );

            $this->fail( 'Exception not thrown' );
        } catch ( Throwable $e ) {
            $this->assertInstanceOf( Error::class, $e );
            $this->assertEquals( 'GET', $method );
            $this->assertEquals(
                'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
                $url
            );
        }
    }

    public function testSaveThrowsException()
    {
        $exception  = Mockery::mock( ConnectException::class );
        $guzzleMock = Mockery::mock( Client::class )
                             ->shouldReceive( 'request' )
                             ->with(
                                 Mockery::capture( $method ),
                                 Mockery::capture( $url ),
                             )
                             ->andThrow( $exception )
                             ->getMock();

        $urlbox   = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );
        $filename = tempnam( '/tmp', 'URLBOX' );

        try {
            $result = $urlbox->save( [
                'url'    => 'https://example.com',
                'format' => 'png'
            ], $filename );

            $this->fail( 'GuzzleException not thrown' );

        } catch ( GuzzleException $e ) {
            $this->assertEquals( 'GET', $method );
            $this->assertEquals(
                'https://api.urlbox.io/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
                $url
            );
            $this->assertEquals( $exception, $e );
        }
    }

    public function testWebhookThrowsExceptionWhenMissingWebhookUrlOption()
    {
        $this->expectException( InvalidArgumentException::class );

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', Mockery::mock( Client::class ) );

        $urlbox->webhook( [
            'url' => 'example.com'
        ] );
    }

    public function testWebhookReturnsApiResponse()
    {
        $responseMock = Mockery::mock( ResponseInterface::class );
        $guzzleMock   = Mockery::mock( Client::class )
                               ->shouldReceive( 'post' )
                               ->with(
                                   Mockery::capture( $url ),
                                   Mockery::capture( $options ),
                               )
                               ->andReturn( $responseMock )
                               ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );

        $response = $urlbox->webhook( [
            'url'         => 'https://example.com',
            'format'      => 'png',
            'webhook_url' => 'https://example.com/webhooks/urlbox',
        ] );

        $this->assertEquals(
            'https://api.urlbox.io/v1/render',
            $url
        );
        $this->assertEquals(
            [
                'headers' => [ 'Authorization' => 'bearer API_SECRET' ],
                'json'    => [
                    'url'         => 'https://example.com',
                    'format'      => 'png',
                    'webhook_url' => 'https://example.com/webhooks/urlbox',
                ]
            ],
            $options
        );
        $this->assertEquals( $responseMock, $response );
    }

    public function testWebhookThrowsGuzzleException()
    {
        $exception  = Mockery::mock( ConnectException::class );
        $guzzleMock = Mockery::mock( Client::class )
                             ->shouldReceive( 'post' )
                             ->with(
                                 Mockery::capture( $url ),
                                 Mockery::capture( $options ),
                             )
                             ->andThrow( $exception )
                             ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', $guzzleMock );


        try {
            $response = $urlbox->webhook( [
                'url'         => 'https://example.com',
                'format'      => 'png',
                'webhook_url' => 'https://example.com/webhooks/urlbox',
            ] );

            $this->fail( 'GuzzleException not thrown' );

        } catch ( GuzzleException $e ) {
            $this->assertEquals(
                'https://api.urlbox.io/v1/render',
                $url
            );
            $this->assertEquals(
                [
                    'headers' => [ 'Authorization' => 'bearer API_SECRET' ],
                    'json'    => [
                        'url'         => 'https://example.com',
                        'format'      => 'png',
                        'webhook_url' => 'https://example.com/webhooks/urlbox',
                    ]
                ],
                $options
            );
            $this->assertEquals( $exception, $e );
        }
    }
}

