<?php
/**
 * Test class for Crunchmail\Entity\MessageEntity
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once('helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Entity\MessageEntity
 */
class MessageEntityTest extends PHPUnit_Framework_TestCase
{
    /**
     * Helpers
     */
    public function checkMessage($msg)
    {
        $this->assertInstanceOf('\Crunchmail\Entities\MessageEntity', $msg);
        $this->assertObjectHasAttribute('body', $msg);
        $this->assertObjectHasAttribute('_links', $msg->body);
        $this->assertInternalType('boolean', $msg->body->track_clicks);
        $this->assertEquals('message_ok', $msg->body->status);
    }

    /**
     * @testdox Valid status should return the translated string
     *
     * @covers ::readableStatus
     */
    public function testValidStatusReturnsString()
    {
        $msg = cm_get_message([['message_ok', '200']]);
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * @testdox Invalid status should return the given string
     *
     * @covers ::readableStatus
     */
    public function testInvalidStatusReturnsString()
    {
        $msg = cm_get_message([['message_html_empty', '200']]);
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * @testdox create() returns a valid result
     *
     * @covers ::post
     * @covers \Crunchmail\Client::apiRequest
     *
     * @todo spy that client call get method on guzzle
     */
    public function testRetrieveReturnsAProperResult()
    {
        $msg = cm_get_message([['message_ok', '200']]);
        $this->checkMessage($msg);
    }

    /**
     * @testdox put() returns a valid result
     *
     * @covers ::put
     * @covers \Crunchmail\Client::apiRequest
     *
     * @todo spy that client call get method on guzzle
     */
    public function testPutReturnsAProperResult()
    {
        $client = cm_mock_client(
            [ ['message_ok', '200'], ['message_ok', '200'] ]
        );
        $msg = $client->messages->get('https://fake');

        $put = $msg->put([]);
        $this->checkMessage($put);
    }

    /**
     * @testdox Method hasBeenSent() works properly
     *
     * @covers ::hasBeenSent
     * @covers ::checkMessage
     */
    public function testMessageHasBeenSent()
    {
        $msg = cm_get_message([['message_sent','200']]);

        $this->assertTrue($msg->hasBeenSent());

        $this->assertFalse($msg->isSending($msg));
        $this->assertFalse($msg->hasIssue($msg));
        $this->assertFalse($msg->isReady($msg));
    }

    /**
     * @testdox Method isSending() works properly
     *
     * @covers ::isSending
     * @covers ::checkMessage
     */
    public function testMessageIsSending()
    {
        $msg = cm_get_message([['message_sending','200']]);

        $this->assertTrue($msg->isSending($msg));

        $this->assertFalse($msg->hasBeenSent());
        $this->assertFalse($msg->hasIssue($msg));
        $this->assertFalse($msg->isReady($msg));
    }

    /**
     * @testdox Method isReady() works properly
     *
     * @covers ::isReady
     * @covers ::checkMessage
     */
    public function testIsReady()
    {
        $msg = cm_get_message([['message_ok', '200']]);

        $this->assertTrue($msg->isReady($msg));

        $this->assertFalse($msg->isSending($msg));
        $this->assertFalse($msg->hasBeenSent());
        $this->assertFalse($msg->hasIssue($msg));
    }

    /**
     * @testdox Method hasError() works properly
     *
     * @covers ::hasIssue
     * @covers ::checkMessage
     */
    public function testMessageHasError()
    {
        $msg = cm_get_message([['message_error', '200']]);

        $this->assertTrue($msg->hasIssue($msg));

        $this->assertFalse($msg->isReady($msg));
        $this->assertFalse($msg->isSending($msg));
        $this->assertFalse($msg->hasBeenSent());
    }
}
