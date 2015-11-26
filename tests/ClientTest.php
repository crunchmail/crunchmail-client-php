<?php
/**
 * Test class for Crunchmail\Client
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Client
 */

require_once('helpers/cm_mock.php');

class ClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    /**
     * Helpers
     */
    function checkMessage($msg)
    {
        $this->assertInstanceOf('stdClass', $msg);
        $this->assertObjectHasAttribute('_links', $msg);
        $this->assertInternalType('boolean', $msg->track_clicks);
        $this->assertEquals('message_ok', $msg->status);
    }

    /**
     * -----------------------------------------------------------------------
     * Tests
     * -----------------------------------------------------------------------
     */

    /**
     * @covers ::__construct
     * @covers ::handleGuzzleException
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
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 401
     */
    public function testInvalidAuthThrowsAnException()
    {
        $msg = cm_get_message('auth_error', 401);
    }

    /**
     * @covers ::__call
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException GuzzleHttp\Exception\RequestException
     * @expectedExceptionCode 0
     */
    public function testInvalidMethodThrowsAnException()
    {
        $client = new Crunchmail\Client(['base_uri' => '']);
        $client->invalidMethod();
    }

    /**
     * @covers ::__call
     * @covers ::handleGuzzleException
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
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 0
     */
    public function testApiOfflineThrowsAnException()
    {
        // no mocking (Connection exception)
        $client = new Crunchmail\Client(['base_uri' => '']);
        $client->retrieve('/fake');
    }

    /*
    public function testApiTimeoutThrowsAnException()
    {
        $this->markTestIncomplete('Todo');
    }
     */

    /**
     * @testdox retrieve() throws an exception on error 500
     *
     * @covers ::retrieve
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testRetrieveInternalServerError()
    {
        cm_mock_client(500)->retrieve('/fake');
    }

    /**
     * @testdox retrieve() throws an exception on error 400
     *
     * @covers ::retrieve
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 404
     */
    public function testRetrieve404Error()
    {
        cm_mock_client(404)->retrieve('/fake');
    }

    /**
     * @testdox udpate() throws an exception on error 500
     *
     * @covers ::update
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testUpdateInternalServerError()
    {
        cm_mock_client(500)->update([]);
    }

    /**
     * @testdox create() throws an exception on error 500
     *
     * @covers ::create
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * 
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testCreateInternalServerError()
    {
        cm_mock_client(500)->create([]);
    }

    /**
     * @covers ::create
     * @covers ::formatResponseOutput
     * @covers ::handleGuzzleException
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
        cm_mock_client(400, 'domain_error')->create([]);
    }

    /**
     * @testdox remove() throws an exception on error 500
     *
     * @covers ::remove
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testRemoveInternalServerError()
    {
        cm_mock_client(500)->remove('/fake');
    }

    /**
     * @covers ::__get
     */
    public function testResourcesAreNotReinstanciated()
    {
        $client = cm_mock_client(200);
        $client->messages->newprop = 1;

        $this->assertEquals(1, $client->messages->newprop);
    }

    /**
     * @testdox create() returns a valid result
     *
     * @covers ::createOrUpdate
     * @covers ::create
     *
     * @todo spy that client call get method on guzzle
     */
    public function testCreateReturnsAProperResult()
    {
        $client = cm_mock_client('200', 'message_ok');
        $msg = $client->create([]);
        $this->checkMessage($msg);
    }

    /**
     * @testdox update() returns a valid result
     *
     * @covers ::createOrUpdate
     * @covers ::update
     *
     * @todo spy that client call get method on guzzle
     */
    public function testUpdateReturnsAProperResult()
    {
        $client = cm_mock_client('200', 'message_ok');
        $msg = $client->create([]);
        $this->checkMessage($msg);
    }

    /**
     * @testdox createOrUpdate() returns a valid result
     *
     * @covers ::createOrUpdate
     * @covers ::create
     *
     * @todo spy that client call get method on guzzle
     */
    public function testRetrieveReturnsAProperResult()
    {
        $msg = cm_get_message('message_ok');
        $this->checkMessage($msg);
    }

    /**
     * @testdox Valid status should return the translated string
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
     * @testdox Invalid status should return the given string
     *
     * @covers ::readableMessageStatus
     */
    public function testInvalidStatusReturnsString()
    {
        $res = Crunchmail\Client::readableMessageStatus('error');
        $this->assertTrue($res === 'error');
    }
}
