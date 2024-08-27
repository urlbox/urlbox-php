<?php

use GuzzleHttp\Client;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
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
            Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) )
        );
    }

    public function testConstructorThrowsInvalidArgumentExceptionWhenApiKeyIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        new Urlbox( '', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );
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
            Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) )
        );
    }

    public function testFromCredentialsThrowsInvalidArgumentExceptionWhenApiKeyIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        Urlbox::fromCredentials( '', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );
    }

    public function testFromCredentialsThrowsInvalidArgumentExceptionWhenApiSecretIsEmpty()
    {
        $this->expectException( InvalidArgumentException::class );
        Urlbox::fromCredentials( 'API_KEY', '', Mockery::mock( Client::class ) );
    }

    public function testRenderReturnsJsonArrayOfApiResponse()
    {
        $guzzleMock = Mockery::mock( Client::class )
                             ->shouldReceive( 'post' )
                             ->with( Mockery::capture( $url ), Mockery::capture( $requestOptions ) )
                             ->andReturn( $this->getMockedGuzzleResponse( '{"renderUrl":"http://storage.foobar.com/urlbox/renders/123456.png","size":525949}' ) )
                             ->getMock();

        $urlbox = new Urlbox( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', $guzzleMock );

        $response = $urlbox->render( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ] );

        $this->assertEquals(
            [ 'renderUrl' => 'http://storage.foobar.com/urlbox/renders/123456.png', 'size' => 525949 ],
            $response
        );
        $this->assertEquals( 'https://api.urlbox.com/v1/render/sync', $url );
        $this->assertEquals( [
            'headers' => [
                'Authorization' => 'Bearer API_SECRET',
            ],
            'json'    => [
                'url'    => 'https://example.com',
                'format' => 'png'
            ]
        ], $requestOptions );
    }

    private function getMockedGuzzleResponse( string $content ): MockInterface
    {
        return Mockery::mock( GuzzleHttp\Psr7\Response::class )
                      ->shouldReceive( 'getBody' )
                      ->andReturn(
                          Mockery::mock( GuzzleHttp\Psr7\Stream::class )
                                 ->shouldReceive( 'getContents' )
                                 ->andReturn( $content )
                                 ->getMock()
                      )
                      ->getMock();
    }

    public function testRenderSavesToDiskAndReturnsJsonArrayOfApiResponseWithLocalPath()
    {
        $guzzleMock = Mockery::mock( Client::class )
                             ->shouldReceive( 'post' )
                             ->with( Mockery::capture( $postUrl ), Mockery::capture( $postRequestOptions ) )
                             ->andReturn( $this->getMockedGuzzleResponse( '{"renderUrl":"http://storage.foobar.com/urlbox/renders/123456.png","size":525949}' ) )
                             ->shouldReceive( 'get' )
                             ->with( Mockery::capture( $getUrl ) )
                             ->andReturn( $this->getMockedGuzzleResponse( 'foo bar' ) )
                             ->getMock();

        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', $guzzleMock );

        $filename = tempnam( '/tmp', 'URLBOX' );

        $result = $urlbox->render( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ], $filename );

        $this->assertEquals(
            [
                'renderUrl' => 'http://storage.foobar.com/urlbox/renders/123456.png',
                'size'      => 525949,
                'localPath' => $filename
            ],
            $result
        );

        $this->assertEquals( 'https://api.urlbox.com/v1/render/sync', $postUrl );
        $this->assertEquals( [
            'headers' => [
                'Authorization' => 'Bearer API_SECRET',
            ],
            'json'    => [
                'url'    => 'https://example.com',
                'format' => 'png'
            ]
        ], $postRequestOptions );
        $this->assertEquals( 'http://storage.foobar.com/urlbox/renders/123456.png', $getUrl );

        $this->assertEquals(
            'foo bar',
            file_get_contents( $filename )
        );
    }

    public function testRenderSyncReturnsJsonArrayOfApiResponse()
    {
        $guzzleMock = Mockery::mock( Client::class )
                             ->shouldReceive( 'post' )
                             ->with( Mockery::capture( $url ), Mockery::capture( $requestOptions ) )
                             ->andReturn( $this->getMockedGuzzleResponse( '{"status":"created","renderId":"00000000-0000-0000-0000-000000000000","statusUrl":"https://api.urlbox.com/render/00000000-0000-0000-0000-000000000000"}' ) )
                             ->getMock();

        $urlbox = new Urlbox( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', $guzzleMock );

        $response = $urlbox->renderAsync( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ] );

        $this->assertEquals(
            [
                'status'    => 'created',
                'renderId'  => '00000000-0000-0000-0000-000000000000',
                'statusUrl' => 'https://api.urlbox.com/render/00000000-0000-0000-0000-000000000000'
            ],
            $response
        );
        $this->assertEquals( 'https://api.urlbox.com/v1/render', $url );
        $this->assertEquals( [
            'headers' => [
                'Authorization' => 'Bearer API_SECRET',
            ],
            'json'    => [
                'url'    => 'https://example.com',
                'format' => 'png'
            ]
        ], $requestOptions );
    }

    public function testGenerateSignedUrlDefaultFormatToPng()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );
        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
            $urlbox->generateSignedUrl( [ 'url' => 'https://example.com' ] )
        );
    }

    public function testGenerateSignedUrlCanSetFormatToPng()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/png?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToJpg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'jpg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/jpg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToJpeg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'jpeg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/jpeg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToAvif()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'avif'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/avif?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToWebp()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'webp'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/webp?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToPdf()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'pdf'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/pdf?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToSvg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'svg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/svg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlCanSetFormatToHtml()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'html'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/5eaae418596fb183174660503df908a3966f4ba5/html?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateSignedUrlEncodesOptionsCorrectly()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateSignedUrl( [
            'url'       => 'https://example.com/~!@#$%^&*(){}[]=:/,;?+\'"\\',
            'format'    => 'png',
            'block_ads' => true
        ] );

        $this->assertEquals(
            "https://api.urlbox.com/v1/API_KEY/897c2361c52a5eb41b9128a2b7e70ffd5fefd662/png?url=https%3A%2F%2Fexample.com%2F~!%40%23%24%25%5E%26*()%7B%7D%5B%5D%3D%3A%2F%2C%3B%3F%2B'%22%5C&block_ads=true",
            $url
        );
    }

    public function testGenerateSignedUrlProducesCorrectUrlForTheKitchenSink()
    {
        $urlbox  = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );
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

        $url = $urlbox->generateSignedUrl( $options );

        $this->assertEquals(
            "https://api.urlbox.com/v1/API_KEY/5280bc0f0fa198eb6fcde9fd3f32280dec496ee3/png?url=https%3A%2F%2Fapp_staging.example.com%2Fmisc%2Ftemplate_preview.php%3Fdsfdsfsdf%26acc%3D79%26cb%3Dba86b4c1%26regions%3D%255B%257B%2522id%2522%253A%2522dsfds%2522%252C%2522data%2522%253A%257B%2522html%2522%253A%2522It%2520works!%2522%257D%252C%2522type%2522%253A%2522html%2522%257D%255D%26state%3Dpublished%26tid%3D7%26sig%3Da642316f7e0ac9d783c30ef30a89bed3204252000319a2789851bc3de65ea216&delay=5000&selector=%23trynow&full_page=true&width=1280&height=1024&cookie=ckplns%3D1&cookie=foo%3Dbar&user_agent=Mozilla%2F5.0%20(iPhone%3B%20CPU%20iPhone%20OS%2010_0%20like%20Mac%20OS%20X)%20AppleWebKit%2F602.1.32%20(KHTML%2C%20like%20Gecko)%20Version%2F10.0%20Mobile%2F14A5261v%20Safari%2F602.1&retina=true&thumb_width=400&crop_width=500&ttl=604800&force=true&headless=false&wait_for=.someel&click=%23tab-specs-trigger&hover=a%5Bhref%3D%22https%3A%2F%2Fgoogle.com%22%5D&bg_color=%23bbbddd&highlight=trump%7Cinauguration&highlightbg=%2311cc77&highlightfg=green&hide_selector=.modal-backdrop%2C%20%23email-roadblock-topographic-modal&flash=true&timeout=40000&s3_path=%2Fpath%2Fto%2Fimage%20with%20space&use_s3=true",
            $url
        );
    }

    public function testGenerateUnsignedUrlDefaultFormatToPng()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );
        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/png?url=https%3A%2F%2Fexample.com',
            $urlbox->generateUnsignedUrl( [ 'url' => 'https://example.com' ] )
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToPng()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'png'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/png?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToJpg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'jpg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/jpg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToJpeg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'jpeg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/jpeg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToAvif()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'avif'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/avif?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToWebp()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'webp'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/webp?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToPdf()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'pdf'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/pdf?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToSvg()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'svg'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/svg?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlCanSetFormatToHtml()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'    => 'https://example.com',
            'format' => 'html'
        ] );

        $this->assertEquals(
            'https://api.urlbox.com/v1/API_KEY/html?url=https%3A%2F%2Fexample.com',
            $url
        );
    }

    public function testGenerateUnsignedUrlEncodesOptionsCorrectly()
    {
        $urlbox = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );

        $url = $urlbox->generateUnsignedUrl( [
            'url'       => 'https://example.com/~!@#$%^&*(){}[]=:/,;?+\'"\\',
            'format'    => 'png',
            'block_ads' => true
        ] );

        $this->assertEquals(
            "https://api.urlbox.com/v1/API_KEY/png?url=https%3A%2F%2Fexample.com%2F~!%40%23%24%25%5E%26*()%7B%7D%5B%5D%3D%3A%2F%2C%3B%3F%2B'%22%5C&block_ads=true",
            $url
        );
    }

    public function testGenerateUnsignedUrlProducesCorrectUrlForTheKitchenSink()
    {
        $urlbox  = Urlbox::fromCredentials( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET', Mockery::mock( Client::class ) );
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

        $url = $urlbox->generateUnsignedUrl( $options );

        $this->assertEquals(
            "https://api.urlbox.com/v1/API_KEY/png?url=https%3A%2F%2Fapp_staging.example.com%2Fmisc%2Ftemplate_preview.php%3Fdsfdsfsdf%26acc%3D79%26cb%3Dba86b4c1%26regions%3D%255B%257B%2522id%2522%253A%2522dsfds%2522%252C%2522data%2522%253A%257B%2522html%2522%253A%2522It%2520works!%2522%257D%252C%2522type%2522%253A%2522html%2522%257D%255D%26state%3Dpublished%26tid%3D7%26sig%3Da642316f7e0ac9d783c30ef30a89bed3204252000319a2789851bc3de65ea216&delay=5000&selector=%23trynow&full_page=true&width=1280&height=1024&cookie=ckplns%3D1&cookie=foo%3Dbar&user_agent=Mozilla%2F5.0%20(iPhone%3B%20CPU%20iPhone%20OS%2010_0%20like%20Mac%20OS%20X)%20AppleWebKit%2F602.1.32%20(KHTML%2C%20like%20Gecko)%20Version%2F10.0%20Mobile%2F14A5261v%20Safari%2F602.1&retina=true&thumb_width=400&crop_width=500&ttl=604800&force=true&headless=false&wait_for=.someel&click=%23tab-specs-trigger&hover=a%5Bhref%3D%22https%3A%2F%2Fgoogle.com%22%5D&bg_color=%23bbbddd&highlight=trump%7Cinauguration&highlightbg=%2311cc77&highlightfg=green&hide_selector=.modal-backdrop%2C%20%23email-roadblock-topographic-modal&flash=true&timeout=40000&s3_path=%2Fpath%2Fto%2Fimage%20with%20space&use_s3=true",
            $url
        );
    }

    public function testVerifyWebhookSignatureThrowsExceptionWhenWebhookSecretNotSet()
    {
        try {
            $urlbox = new Urlbox( 'API_KEY', 'API_SECRET' );
            $urlbox->verifyWebhookSignature( 't=1,sha256=foobar', '' );

            $this->fail( 'Expected Exception not thrown' );
        } catch ( Exception $exception ) {
            $this->assertEquals(
                'Unable to verify signature as Webhook Secret is not set. You can find your webhook secret inside your project\'s settings - https://www.urlbox.com/dashboard/projects',
                $exception->getMessage()
            );
        }
    }

    public function testVerifyWebhookSignatureThrowsExceptionWhenHeaderIsEmpty()
    {
        try {
            $urlbox = new Urlbox( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET' );
            $urlbox->verifyWebhookSignature( '', '' );

            $this->fail( 'Expected Exception not thrown' );
        } catch ( InvalidArgumentException $exception ) {
            $this->assertEquals(
                'Unable to verify signature as header is empty. Please ensure you pass the `x-urlbox-signature` from the header of the webhook response',
                $exception->getMessage()
            );
        }
    }

    public function testVerifyWebhookSignatureReturnsTrueWhenSignatureMatches()
    {
        $content = '{"event": "render.succeeded","renderId": "19a59ab6-a5aa-4cde-86cb-d2b23302fd84","result": {"renderUrl": "https://renders.urlbox.com/urlbox1/renders/6215a3df94d7588f7d910513/2022/7/6/19a59ab6-a5aa-4cde-86cb-d2b23302fd84.png","size": 34097},"meta": {"startTime": "2022-07-06T17:49:18.593Z","endTime": "2022-07-06T17:49:21.103Z"}}';
        $header  = 't=1657129761,sha256=ddbceae3998704c0b264d8e8c1d486df9f1c0b6cdb77e6e13ce7de4a72fbd81d';

        $urlbox = new Urlbox( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET' );

        $this->assertTrue( $urlbox->verifyWebhookSignature( $header, $content ) );
    }

    public function testVerifyWebhookSignatureReturnsFalseWhenSignatureDoNotMatch()
    {
        $content = '{"event": "render.succeeded","renderId": "19a59ab6-a5aa-4cde-86cb-d2b23302fd84","result": {"renderUrl": "https://renders.urlbox.com/urlbox1/renders/6215a3df94d7588f7d910513/2022/7/6/19a59ab6-a5aa-4cde-86cb-d2b23302fd84.png","size": 34097},"meta": {"startTime": "2022-07-06T17:49:18.593Z","endTime": "2022-07-06T17:49:21.103Z"}}';
        $header  = 't=1657129761,sha256=foobare3998704c0b264d8e8c1d4foobar1c0b6cdb77e6e13ce7de4a72foobar';

        $urlbox = new Urlbox( 'API_KEY', 'API_SECRET', 'WEBHOOK_SECRET' );

        $this->assertFalse( $urlbox->verifyWebhookSignature( $header, $content ) );
    }

    public function testVerifyWebhookSignatureReturnsFalseWhenWebhookSecretIncorrect()
    {
        $content = '{"event": "render.succeeded","renderId": "19a59ab6-a5aa-4cde-86cb-d2b23302fd84","result": {"renderUrl": "https://renders.urlbox.com/urlbox1/renders/6215a3df94d7588f7d910513/2022/7/6/19a59ab6-a5aa-4cde-86cb-d2b23302fd84.png","size": 34097},"meta": {"startTime": "2022-07-06T17:49:18.593Z","endTime": "2022-07-06T17:49:21.103Z"}}';
        $header  = 't=1657129761,sha256=ddbceae3998704c0b264d8e8c1d486df9f1c0b6cdb77e6e13ce7de4a72fbd81d';

        $urlbox = new Urlbox( 'API_KEY', 'API_SECRET', 'INCORRECT_WEBHOOK_SECRET' );

        $this->assertFalse( $urlbox->verifyWebhookSignature( $header, $content ) );
    }


}

