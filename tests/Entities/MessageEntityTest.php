<?php
/**
 * Test class for Crunchmail\Entity\MessageEntity
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 */
class MessageEntityTest extends \Crunchmail\Tests\TestCase
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

    public function testToStringReturnsMessageName()
    {
        $client = $this->quickMock(['message_ok', '200']);
        $msg = $client->messages->get('https://fake');
        $this->assertEquals( (string) $msg, $msg->body->name);
    }

    /**
     * @testdox Valid status should return the translated string
     */
    public function testValidStatusReturnsString()
    {
        $client = $this->quickMock(['message_ok', '200']);
        $msg = $client->messages->get('https://fake');
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * @testdox Invalid status should return the given string
     */
    public function testInvalidStatusReturnsString()
    {
        $client = $this->quickMock(['message_html_empty', '200']);
        $msg = $client->messages->get('https://fake');
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * @testdox create() returns a valid result
     *
     * @todo spy that client call get method on guzzle
     */
    public function testRetrieveReturnsAProperResult()
    {
        $client = $this->quickMock(['message_ok', '200']);
        $msg = $client->messages->get('https://fake');
        $this->checkMessage($msg);
    }

    /**
     * @testdox put() returns a valid result
     *
     * @todo spy that client call get method on guzzle
     */
    public function testPutReturnsAProperResult()
    {
        $client = $this->quickMock(
            ['message_ok', '200'],
            ['message_ok', '200']
        );
        $msg = $client->messages->get('https://fake');
        $put = $msg->put([]);

        $this->checkMessage($put);
    }

    /**
     * @testdox Method hasBeenSent() works properly
     */
    public function testMessageHasBeenSent()
    {
        $client = $this->quickMock(['message_sent', '200']);
        $msg = $client->messages->get('https://fake');

        $this->assertTrue($msg->hasBeenSent());

        $this->assertFalse($msg->isSending($msg));
        $this->assertFalse($msg->hasIssue($msg));
        $this->assertFalse($msg->isReady($msg));
    }

    /**
     * @testdox Method isSending() works properly
     */
    public function testMessageIsSending()
    {
        $client = $this->quickMock(['message_sending', '200']);
        $msg = $client->messages->get('https://fake');

        $this->assertTrue($msg->isSending($msg));

        $this->assertFalse($msg->hasBeenSent());
        $this->assertFalse($msg->hasIssue($msg));
        $this->assertFalse($msg->isReady($msg));
    }

    /**
     * @testdox Method isReady() works properly
     */
    public function testIsReady()
    {
        $client = $this->quickMock(['message_ok', '200']);
        $msg = $client->messages->get('https://fake');

        $this->assertTrue($msg->isReady($msg));

        $this->assertFalse($msg->isSending($msg));
        $this->assertFalse($msg->hasBeenSent());
        $this->assertFalse($msg->hasIssue($msg));
    }

    /**
     * @testdox Method hasError() works properly
     */
    public function testMessageHasError()
    {
        $client = $this->quickMock(['message_error', '200']);
        $msg = $client->messages->get('https://fake');

        $this->assertTrue($msg->hasIssue($msg));

        $this->assertFalse($msg->isReady($msg));
        $this->assertFalse($msg->isSending($msg));
        $this->assertFalse($msg->hasBeenSent());
    }

    /**
     * @todo test that send() is stateless
     */
    public function testSendingAMessageReturnsAValidResponse()
    {
        $client = $this->quickMock(
            ['message_ok', '200'],
            ['message_sending', '200']
        );

        $message = $client->messages->get('https://fake');

        $message = $message->send();

        $history = $this->getHistory();

        $this->assertInstanceOf('\Crunchmail\Entities\MessageEntity', $message);
        $this->assertTrue($message->isSending());

        $this->assertEquals(2, count($history));

        $req = $history[0]['request'];
        $this->assertEquals('GET', $req->getMethod());
        $this->assertEquals('https://fake', (string) $req->getUri());

        $req = $history[1]['request'];
        $this->assertEquals('PATCH', $req->getMethod());
        $this->assertRegExp('#.*/messages/[0-9]+/$#', (string) $req->getUri());
    }

}
