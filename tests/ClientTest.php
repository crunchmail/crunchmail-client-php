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

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

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
        $client = new Client(['base_uri' => '']);
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
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingAnUnknowResourceThrowsAnException()
    {
        $handler = $this->mockHandler(['empty', '200']);
        $client  = $this->mockClient($handler);
        $client->invalid->test = 1;
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
        $handler = $this->mockHandler(['empty', '200']);
        $client  = $this->mockClient($handler);

        $this->assertResource($client->$resource);
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
        $handler = $this->mockHandler(['message_ok', '200']);
        $client  = $this->mockClient($handler);

        $result = $client->apiRequest('get', 'https://fake');

        $this->assertInstanceOf('stdClass', $result);
    }

    /**
     * @covers ::apiRequest
     */
    public function testRelativeUriRequestReturnAValidObject()
    {
        $handler = $this->mockHandler(['message_ok', '200']);
        $client  = $this->mockClient($handler);

        $result = $client->apiRequest('get', 'fake');

        $this->assertInstanceOf('stdClass', $result);
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
        $handler = $this->mockHandler([$tpl, $code]);
        $client  = $this->mockClient($handler);
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
            $handler = $this->mockHandler([$tpl, $code]);
            $client  = $this->mockClient($handler);
            $client->apiRequest($method, '/fake');
        }
        catch (\Exception $e)
        {
            $this->assertEquals($code, $e->getCode());
            return;
        }
        $this->fail('An expected exception has not been raised');
    }
}
