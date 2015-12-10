<?php
/**
 * Test class for Crunchmail\Client
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once('helpers/cm_mock.php');

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Client
 */
class ClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * -----------------------------------------------------------------------
     * Tests
     * -----------------------------------------------------------------------
     */

    /**
     * @covers ::__construct
     * @covers ::catchGuzzleException
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testInvalidConfigurationThrowsAnException()
    {
        $client = new Crunchmail\Client([]);
    }

    /**
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 401
     */
    public function testInvalidAuthThrowsAnException()
    {
        $msg = cm_get_message(['auth_error' => '401']);
    }

    /**
     * @covers ::__construct
     * @covers ::catchGuzzleException
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 0
     */
    public function testInvalidMethodThrowsAnException()
    {
        $client = new Crunchmail\Client(['base_uri' => '']);
        $client->invalidMethod();
    }

    /**
     * @covers ::catchGuzzleException
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testUnknowPropertyThrowsAnException()
    {
        $client = new Crunchmail\Client(['base_uri' => '']);
        $client->invalidProperty->test();
    }

    /**
     * @covers ::catchGuzzleException
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testUnexpectedErrors()
    {
        $responses = [ new MockHandler([ new RuntimeException('Oops!') ]) ];

        // Create a mock and queue responses.
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);

        $client->apiRequest('get', 'fake');
    }

    /**
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 0
     */
    public function testApiOfflineThrowsAnException()
    {
        // no mocking (Connection exception)
        $client = new Crunchmail\Client(['base_uri' => '']);
        $client->apiRequest('get', '/fake');
    }

    /**
     * @testdox retrieve() throws an exception on error 500
     *
     * @covers ::retrieve
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testRetrieveInternalServerError()
    {
        cm_mock_client(['empty' => '500'])->apiRequest('get', '/fake');
    }

    /**
     * @testdox retrieve() throws an exception on error 400
     *
     * @covers ::retrieve
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 404
     */
    public function testRetrieve404Error()
    {
        cm_mock_client(['empty' => '404'])->apiRequest('get', '/fake');
    }

    /**
     * @testdox udpate() throws an exception on error 500
     *
     * @covers ::update
     * @covers ::apiRequest
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testUpdateInternalServerError()
    {
        cm_mock_client(['empty' => '500'])->apiRequest('put', '/fake');
    }

    /**
     * @testdox create() throws an exception on error 500
     *
     * @covers ::create
     * @covers ::apiRequest
     * @covers ::catchGuzzleException
     * 
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testCreateInternalServerError()
    {
        cm_mock_client(['empty' => '500'])->apiRequest('post', '/fake');
    }

    /**
     * @covers ::create
     * @covers ::apiRequest
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     *
     * @group bug-2030
     * @todo update template when API has been fixed
     */
    public function testCreateOnInvalidDomainsThrowsAnException()
    {
        cm_mock_client(['domain_error' => '400'])->apiRequest('post', '/fake');
    }

    /**
     * @testdox remove() throws an exception on error 500
     *
     * @covers ::remove
     * @covers ::apiRequest
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testRemoveInternalServerError()
    {
        cm_mock_client(['empty' => '500'])->apiRequest('delete', '/fake');
    }

    /**
     * @covers ::__get
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testAccessingUnknowPropertyThrowsAnException()
    {
        $client = cm_mock_client(['empty' => '200']);
        $client->invalid->test = 1;
    }
}
