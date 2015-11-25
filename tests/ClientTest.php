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
     * -----------------------------------------------------------------------
     * Tests
     * -----------------------------------------------------------------------
     */

    /**
     * @covers ::__construct
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * @expectedException RuntimeException
     */
    public function testInvalidConfigurationThrowsAnException()
    {
        $client = new Crunchmail\Client([]);
    }

    /**
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
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
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
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
     * Check that last 404 error is saved
     *
     * @covers ::getLastError
     * @covers ::getLastErrorCode
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     */
    public function testLastErrorIsSaved()
    {
        $client = cm_mock_client(404, 'empty');

        try
        {
            $client->retrieve('/fake');
        }
        catch (\Exception $e)
        {
            $this->assertEquals(404, Crunchmail\Client::getLastErrorCode());
            $this->assertContains('404', Crunchmail\Client::getLastError());
        }
    }

    /**
     * @testdox createOrUpdate() returns a valid result
     * @covers ::createOrUpdate
     * @covers ::create
     *
     * @todo spy that client call get method on guzzle
     */
    public function testCreateOrUpdate()
    {
        $msg = cm_get_message('message_ok');
        $this->assertObjectHasAttribute('_links', $msg);
        $this->assertInternalType('boolean', $msg->track_clicks);
        $this->assertEquals('message_ok', $msg->status);
    }

    /**
     * @testdox retrieve() throws an exception on error 500
     * @covers ::retrieve
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testRetrieveInternalServerError()
    {
        cm_mock_client(500)->retrieve('/fake');
    }

    /**
     * @testdox retrieve() throws an exception on error 400
     * @covers ::retrieve
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 404
     */
    public function testRetrieve404Error()
    {
        cm_mock_client(404)->retrieve('/fake');
    }

    /**
     * @testdox udpate() throws an exception on error 500
     * @covers ::update
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testUpdateInternalServerError()
    {
        cm_mock_client(500)->update('/fake');
    }

    /**
     * @testdox create() throws an exception on error 500
     * @covers ::create
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testCreateInternalServerError()
    {
        cm_mock_client(500)->create('/fake');
    }

    /**
     * @testdox remove() throws an exception on error 500
     * @covers ::remove
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testRemoveInternalServerError()
    {
        cm_mock_client(500)->remove('/fake');
    }

    /**
     * @testdox getLastRawError returns the last exception
     * @covers ::getLastRawError
     */
    public function testGetLatRawError()
    {
        try
        {
            cm_mock_client(404, 'message_error')->remove('/fake');
        }
        catch (\Exception $e)
        {
            $err  = Crunchmail\Client::getLastRawError();
        }

        $this->assertInstanceOf('\stdClass', $err);
    }
    /**
     * @testdox Exception generates a proper error message and error code
     * @covers ::getLastError
     * @covers ::getLastErrorCode
     * @covers ::handleGuzzleException
     * @covers ::catchGuzzleException
     */
    public function testGetLastError()
    {
        try
        {
            cm_mock_client(500)->remove('/fake');
        }
        catch (\Exception $e)
        {
            $err  = Crunchmail\Client::getLastError();
            $code = Crunchmail\Client::getLastErrorCode();
        }

        $this->assertInternalType('string', $err);
        $this->assertTrue($err !== '');
        $this->assertEquals(500, $code);
    }

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
