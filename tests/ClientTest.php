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
     * Providers
     */

    /**
     * Returns an empty client
     *
     * @return array
     */
    public function emptyClientProvider()
    {
        return [
            [new Crunchmail\Client(['base_uri' => ''])]
        ];
    }

    /**
     * Returns error situations
     *
     * @return array
     */
    public function errorCodesProvider()
    {
        return [
            ['empty', '400', 'get'],
            ['empty', '400', 'post'],
            ['domain_error', '400', 'post'],
            ['empty', '400', 'delete'],
            ['empty', '500', 'get'],
            ['empty', '500', 'post'],
            ['empty', '501', 'get']
        ];
    }
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
     * @covers ::__construct
     * @covers ::catchGuzzleException
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 0
     * @dataProvider emptyClientProvider
     */
    public function testInvalidMethodThrowsAnException($client)
    {
        $client->invalidMethod();
    }

    /**
     * @covers ::catchGuzzleException
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     * @dataProvider emptyClientProvider
     */
    public function testUnknowPropertyThrowsAnException($client)
    {
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
     * @dataProvider errorCodesProvider
     * @expectedException Crunchmail\Exception\ApiException
     */
    public function testResponseErrorThrowsAnException($tpl, $code, $method)
    {
        cm_mock_client([[$tpl, $code]])->apiRequest($method, '/fake');
    }

    /**
     * @dataProvider errorCodesProvider
     */
    public function testResponseErrorCodes($tpl, $code, $method)
    {
        try
        {
            cm_mock_client([[$tpl, $code]])->apiRequest($method, '/fake');
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
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testAccessingUnknowPropertyThrowsAnException()
    {
        $client = cm_mock_client([['empty', '200']]);
        $client->invalid->test = 1;
    }
}
