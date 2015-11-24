<?php
/**
 * TODO: Test Exception result for unexpected errors
 * TODO: Test Exception result for API errors
 * TODO: Test empty base_uri / invalid conifguration
 * TODO: Test invalid auth
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Client
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
     *
     * @covers ::__construct
     */
    public function testInvalidConfigurationThrowException()
    {
        $client = new Crunchmail\Client([]);
    }

    /**
     * @expectedException GuzzleHttp\Exception\RequestException
     *
     * @covers ::__call
     */
    public function testInvalidMethodThrowException()
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

    public function testCreate()
    {
    }

    /**
     * @covers ::createOrUpdate
     */
    public function testCreateOrUpdate()
    {
        $this->markTestIncomplete('Todo');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @covers ::retrieve
     */
    public function testRetrieveInternalServerError()
    {
        $client = $this->prepareTestException(500);
        $res = $client->retrieve('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @covers ::retrieve
     */
    public function testRetrieve404Error()
    {
        $client = $this->prepareTestException(404);
        $res = $client->retrieve('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @covers ::update
     */
    public function testUpdateInternalServerError()
    {
        $client = $this->prepareTestException(500);
        $res = $client->update('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @covers ::create
     */
    public function testCreateInternalServerError()
    {
        $client = $this->prepareTestException(500);
        $res = $client->create('/fake');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @covers ::remove
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
     *
     * @covers ::readableMessageStatus
     */
    public function testValidStatusReturnsString()
    {
        $res = Crunchmail\Client::readableMessageStatus('message_ok');
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * Invalid status should return the given string
     *
     * @covers ::readableMessageStatus
     */
    public function testInvalidStatusReturnsString()
    {
        $res = Crunchmail\Client::readableMessageStatus('error');
        $this->assertTrue($res === 'error');
    }
}
