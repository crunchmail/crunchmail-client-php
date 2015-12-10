<?php
/**
 * Test class for Crunchmail\Client
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Test class
 */
class ClientTest extends \Crunchmail\Tests\TestCase
{
    /*
     * Providers
     */

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

    /*
     * -----------------------------------------------------------------------
     * Tests
     * -----------------------------------------------------------------------
     */

    /**
     * Test empty client creation (for errors)
     *
     * @return array
     */
    public function testEmptyClientReturnsAResponse()
    {
        $client = new Crunchmail\Client(['base_uri' => '']);
        $this->assertInstanceOf('\Crunchmail\Client', $client);
        return $client;
    }

    /**
     * @depends testEmptyClientReturnsAResponse
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 0
     */
    public function testClientThrowsAnExceptionIfApiIsOffline($client)
    {
        $client->apiRequest('get', '/fake');
    }

    /**
     * @depends testEmptyClientReturnsAResponse
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 0
     */
    public function testInvalidMethodThrowsAnException($client)
    {
        $client->invalidMethod();
    }

    /**
     * @depends testEmptyClientReturnsAResponse
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testUnknowPropertyThrowsAnException($client)
    {
        $client->invalidProperty->test();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testInvalidConfigurationThrowsAnException()
    {
        $client = new Crunchmail\Client([]);
    }

    /**
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testUnexpectedErrorsThrowsRuntimeException()
    {
        $responses = [ new MockHandler([ new RuntimeException('Oops!') ]) ];
        $mock      = new MockHandler($responses);
        $handler   = HandlerStack::create($mock);

        $client  = $this->mockClient($handler);

        $client->apiRequest('get', 'fake');
    }

    /**
     * @dataProvider errorCodesProvider
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

    /**
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testAccessingUnknowPropertyThrowsAnException()
    {
        $handler = $this->mockHandler(['empty', '200']);
        $client  = $this->mockClient($handler);
        $client->invalid->test = 1;
    }
}
