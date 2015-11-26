<?php
/**
 * Test class for Crunchmail\Messages
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
require_once('helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Messages
 */
class MessagesTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    /**
     * Create a mocked client and execute requested method
     *
     * @param string $method    method to test
     * @param string $tpl       template name
     * @param string $param     method param
     * @param int    $code      http status code of response
     */
    protected function prepareCheck($method, $tpl, $param, $code=200)
    {
    }

    /**
     * @testdox Method create() works properly
     *
     * @covers ::create
     */
    public function testCreateAMessage()
    {
        $client = cm_mock_client(200, 'message_ok');
        $result = $client->messages->create([]);

        $this->assertEquals('message_ok', $result->status);
    }

    /**
     * @testdox Method update() works properly
     *
     * @covers ::update
     */
    public function testUpdateAMessage()
    {
        $client = cm_mock_client(200, 'message_ok');
        $result = $client->messages->update([]);

        $this->assertEquals('message_ok', $result->status);
    }

    /**
     * @testdox Method hasBeenSent() works properly
     *
     * @covers ::hasBeenSent
     * @covers ::checkMessage
     */
    public function testMessageHasBeenSent()
    {
        $msg = cm_get_message('message_sent');
        $this->assertTrue(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
        $this->assertFalse(Crunchmail\Messages::isReady($msg));
    }

    /**
     * @testdox Method isSending() works properly
     *
     * @covers ::isSending
     * @covers ::checkMessage
     */
    public function testMessageIsSending()
    {
        $msg = cm_get_message('message_sending');
        $this->assertTrue(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::isReady($msg));
    }

    /**
     * @testdox Method isReady() works properly
     *
     * @covers ::isReady
     * @covers ::checkMessage
     */
    public function testIsReady()
    {
        $msg = cm_get_message('message_ok');
        $this->assertTrue(Crunchmail\Messages::isReady($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
    }

    /**
     * @testdox Method hasError() works properly
     *
     * @covers ::hasIssue
     * @covers ::checkMessage
     */
    public function testMessageHasError()
    {
        $msg = cm_get_message('message_error');
        $this->assertTrue(Crunchmail\Messages::hasIssue($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::isReady($msg));
    }

    /**
     * @testdox Method hasBeenSent() throws an exception on invalid parameter
     *
     * @covers ::hasBeenSent
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testHasBeenSentThrowsAnExceptionOnInvalidParameter()
    {
        Crunchmail\Messages::hasBeenSent('error');
    }

    /**
     * @testdox Method isSending() throws an exception on invalid parameter
     *
     * @covers ::isSending
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testIsSendingThrowsAnExceptionOnInvalidParameter()
    {
        Crunchmail\Messages::isSending('error');
    }

    /**
     * @testdox Method isReady() throws an exception on invalid parameter
     *
     * @covers ::isReady
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testIsReadyThrowsAnExceptionOnInvalidParameter()
    {
        Crunchmail\Messages::isReady('error');
    }

    /**
     * @testdox Method hasIssue() throws an exception on invalid parameter
     *
     * @covers ::hasIssue
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testMessageHasIssueThrowsAnException()
    {
        Crunchmail\Messages::hasIssue('error');
    }

    /**
     * @testdox Method create() throws an exception on invalid domain
     *
     * @covers ::create
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testCreateWithInvalidDomain()
    {
        $client = cm_mock_client(400, 'domains_invalid_mx');
        $result = $client->messages->create([]);
    }

    /**
     * @covers ::getPreviewUrl
     */
    public function testGetPreviewUrlReturnsUrl()
    {
        $client = cm_mock_client(200, 'message_ok');
        $res = $client->messages->getPreviewUrl('fakeid');
        $this->assertStringStartsWith('http', $res);
        $this->assertStringEndsWith('preview_send/', $res);
    }

    /**
     * @covers ::sendPreview
     */
    public function testSendingPreviewReturnsAValidResponse()
    {
        $container = [];
        $client = cm_mock_client(200, ['message_ok', 'message_ok'],
            $container);

        $res = $client->messages->sendPreview('https://testid', 'f@fake.fr');

        $this->assertTrue(\Crunchmail\Messages::isReady($res));

        // checking requests
        $this->assertEquals(2, count($container));

        // checking getPreviw request
        $reqUrl = $container[0]['request'];
        $this->assertEquals('GET', $reqUrl->getMethod());
        $this->assertEquals('https://testid', (string) $reqUrl->getUri());

        // checking sending preview request
        $reqSend = $container[1]['request'];
        $this->assertEquals('POST', $reqSend->getMethod());

        // check that the preview url has been used
        $this->assertStringEndsWith('preview_send/', (string)
            $reqSend->getUri());
    }

    /**
     * @covers ::sendMessage
     */
    public function testSendingAMessageReturnsAValidResponse()
    {
        $container = [];
        $client = cm_mock_client(200, ['message_sending'], $container);

        $res = $client->messages->sendMessage('https://testid');

        $this->assertInstanceOf('stdClass', $res);
        $this->assertTrue(\Crunchmail\Messages::isSending($res));

        $req = $container[0]['request'];
        $this->assertEquals(1, count($container));
        $this->assertEquals('PATCH', $req->getMethod());
        $this->assertEquals('https://testid', (string) $req->getUri());
    }

    /**
     * @covers ::sendPreview
     *
     * @expectedExceptionCode 500
     * @expectedException Crunchmail\Exception\ApiException
     */
     public function testGetPreviewUrlError()
     {
        $client = cm_mock_client(500, ['message_error', 'message_error']);
        $res = $client->messages->sendPreview('https://testid', 'f@fake.fr');
     }

}
