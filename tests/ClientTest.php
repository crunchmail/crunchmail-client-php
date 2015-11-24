<?php
/**
 * TODO: Test Exception result for unexpected errors
 * TODO: Test Exception result for API errors
 * TODO: Test empty base_uri / invalid conifguration
 * TODO: Test invalid auth
 */
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class ClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    /**
     * -----------------------------------------------------------------------
     * Helpers
     * -----------------------------------------------------------------------
     */
    private function prepareTestException($code)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([ new Response($code, []) ]);

        $handler = HandlerStack::create($mock);
        $client = new Crunchmail\Client(['base_uri' => '', 'handler' =>
            $handler]);

        return $client;
    }

    /**
     * -----------------------------------------------------------------------
     * Tests
     * -----------------------------------------------------------------------
     */

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidConfiguration()
    {
        $client = new Crunchmail\Client([]);
    }

    /**
     * @expectedException GuzzleHttp\Exception\RequestException
     */
    public function testInvalidMethod()
    {
        $client = new Crunchmail\Client(['base_uri' => '']);
        $client->invalidMethod();
    }

    public function testAPIOffline()
    {
    }

    public function testAPITimeout()
    {
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     */
    public function testRetrieveInternalServerError()
    {
        $client = $this->prepareTestException(500);
        $res = $client->retrieve('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     */
    public function testRetrieve404Error()
    {
        $client = $this->prepareTestException(404);
        $res = $client->retrieve('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     */
    public function testUpdateInternalServerError()
    {
        $client = $this->prepareTestException(500);
        $res = $client->update('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     */
    public function testCreateInternalServerError()
    {
        $client = $this->prepareTestException(500);
        $res = $client->create('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     */
    /*
    public function testRemoveInternalServerError()
    {
        $client = $this->prepareTestException('Crunchmail\Exception\ApiException');
        $res = $client->remove('/fake');
    }
    */

    /**
     * Invalid status should return the translated string
     */
    public function testReadableMessageStatus()
    {
        $res = Crunchmail\Client::readableMessageStatus('message_ok');
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * Invalid status should return the given string
     */
    public function testInvalidReadableMessageStatus()
    {
        $res = Crunchmail\Client::readableMessageStatus('error');
        $this->assertTrue($res === 'error');
    }
}
