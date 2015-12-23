<?php
/**
 * Test class for Crunchmail\Client
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail\Client;
use Crunchmail\PHPUnit\TestCase;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

use GuzzleHttp\Psr7\Response;

/**
 * Test class
 *
 * @covers \Crunchmail\Client
 * @coversDefaultClass \Crunchmail\Client
 */
class ClientTest extends TestCase
{
    /* ---------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */

    /**
     * Returns error situations
     *
     * @return array
     */
    public function errorCodesProvider()
    {
        return [
            ['empty', '400', 'get'],
            ['empty', '404', 'get'],
            ['empty', '400', 'post'],
            ['domain_error', '400', 'post'],
            ['empty', '400', 'delete'],
            ['empty', '500', 'get'],
            ['empty', '500', 'post'],
            ['empty', '500', 'patch'],
            ['empty', '500', 'put'],
            ['empty', '501', 'get']
        ];
    }

    public function uriProvider()
    {
        return [
            ['http://unsecure.url'],
            ['https://secure.url'],
            ['relative'],
            ['/relative'],
            ['/relative/'],
            ['relative/'],
            ['']
        ];
    }

    public function resourceProvider()
    {
        $list = [];
        foreach (array_keys(Client::$paths) as $path)
        {
            $list[] = [$path];
        }
        return $list;
    }

    /* ---------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * Test empty client creation (for errors)
     *
     * @return array
     *
     * @covers ::__construct
     */
    public function testEmptyClientReturnsAResponse()
    {
        $client = new Client(['base_uri' => '', 'token_uri' => '']);
        $this->assertInstanceOf('Crunchmail\Client', $client);
        return $client;
    }

    /**
     * @covers ::__construct
     *
     * @expectedExceptionCode 0
     * @expectedException RuntimeException
     */
    public function testInvalidConfigurationThrowsAnException()
    {
        new Client([]);
    }

    /**
     * @covers ::__construct
     *
     * @expectedExceptionCode 0
     * @expectedException RuntimeException
     */
    public function testInvalidTokenUriThrowsAnException()
    {
        new Client(['base_uri' => '']);
    }

    /**
     * @depends testEmptyClientReturnsAResponse
     * @covers ::__call
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionCode 0
     */
    public function testInvalidMethodThrowsAnException($client)
    {
        $client->invalidMethod();
    }

    /**
     * @depends testEmptyClientReturnsAResponse
     * @covers ::__get
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 404
     */
    public function testAccessingAnUnknowResourceThrowsAnException()
    {
        $handler = $this->mockHandler(['empty', '404']);
        $client  = $this->mockClient($handler);
        $client->invalid->get();
    }

    /**
     * @dataProvider resourceProvider
     * @covers ::createResource
     * @covers ::__get
     *
     * @todo test instance of specific resource
     * @todo test generic resources creation
     */
    public function testAccessingAResourceReturnsAResource($resource)
    {
        $handler = $this->mockHandler(['message_ok', '200']);
        $client  = $this->mockClient($handler);

        $this->assertGenericResource($client->$resource);
    }

    /**
     * @covers ::apiRequest
     *
     * @expectedExceptionCode 0
     * @expectedException RuntimeException
     *
     * @SuppressWarnings(PHPMD)
     */
    public function testUnexpectedErrorsThrowsRuntimeException()
    {
        $responses = [ new MockHandler([ new \RuntimeException('Oops!') ]) ];
        $mock      = new MockHandler($responses);
        $handler   = HandlerStack::create($mock);

        $client  = $this->mockClient($handler);

        $client->apiRequest('get', 'fake');
    }

    /**
     * @covers ::apiRequest
     */
    public function testAbsoluteUriRequestReturnAValidObject()
    {
        $client = $this->quickMock(['message_ok', 200]);

        $result = $client->apiRequest('get', 'https://fake');

        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     * @covers ::apiRequest
     * @dataProvider uriProvider
     */
    public function testUrlAreSentProperly($uri)
    {
        $client = $this->quickMock(['message_ok', 200]);

        $result = $client->apiRequest('get', $uri);

        $req = $this->getHistoryRequest(0);
        $this->assertEquals((string) $req->getUri(), $uri);
        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     * @covers ::apiRequest
     */
    public function testQueryStringIsSent()
    {
        $query = ['test' => 'works'];

        $cli = $this->quickMock(['messages', 200]);
        $cli->apiRequest('get', '', [], $query);

        $req = $this->getHistoryRequest(0);

        $this->assertEquals('test=works', $req->getUri()->getQuery());
    }

    /**
     * @covers ::apiRequest
     */
    public function testQueryStringIsMergedWithUri()
    {
        $query  = ['test'   => 'works'];

        $cli = $this->quickMock(['messages', 200]);
        $cli->apiRequest('get', '?search=hello', [], $query);

        $req = $this->getHistoryRequest(0);
        $sent = $req->getUri()->getQuery();

        $this->assertContains('test=works', $sent);
        $this->assertContains('search=hello', $sent);
    }

    /**
     * @depends testEmptyClientReturnsAResponse
     * @covers ::apiRequest
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 0
     */
    public function testClientThrowsAnExceptionIfApiIsOffline($client)
    {
        $client->apiRequest('get', '/fake');
    }

    /**
     * @dataProvider errorCodesProvider
     * @covers ::apiRequest
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     */
    public function testResponseErrorThrowsAnException($tpl, $code, $method)
    {
        $client = $this->quickMock([$tpl, $code]);
        $client->apiRequest($method, '/fake');
    }

    /**
     * @dataProvider errorCodesProvider
     * @covers ::apiRequest
     */
    public function testResponseErrorCodesAreCorrect($tpl, $code, $method)
    {
        try
        {
            $client = $this->quickMock([$tpl, $code]);
            $client->apiRequest($method, '/fake');
        }
        catch (\Exception $e)
        {
            $this->assertEquals($code, $e->getCode());
            return;
        }
        $this->fail('An expected exception has not been raised');
    }

    /**
     * @covers ::__get
     */
    public function testThatMappedPathWork()
    {
        $cli = $this->quickMock(['message_ok', 200]);
        $cli->recipients->get();

        $req = $this->getHistoryRequest(0);
        $this->assertEquals('mails/', (string) $req->getUri());
    }

    /**
     * @covers ::tokenAuth
     */
    public function testTokenAuthWithAPIKeyWorks()
    {
        $data = $this->getStdTemplate('token_ok');

        $cli = $this->quickMock(['token_ok', 200]);
        $token = $cli->getToken();

        $req = $this->getHistoryContent(0);

        // check sent request
        $this->assertEquals('key', $req->api_key);
        $this->assertEquals($data->token, $token);
    }

    /**
     * @covers ::credentialsAuth
     */
    public function testTokenAuthWithCredentialsKeyWorks()
    {
        $data = $this->getStdTemplate('token_ok');

        $cli = $this->quickMock(['token_ok', 200]);
        $token = $cli->getTokenFromCredentials('user', 'password');

        $req = $this->getHistoryContent(0);

        // check sent request
        $this->assertEquals('user', $req->identifier);
        $this->assertEquals('password', $req->password);

        $this->assertEquals($data->token, $token);
    }
}
