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
    /*
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

    public function checkSentHistory($method, $i=1, $reg='#.*/messages/[0-9]+/$#')
    {
        $history = $this->getHistory();

        $req = $history[1]['request'];
        $this->assertEquals($method, $req->getMethod());
        $this->assertRegExp($reg, (string) $req->getUri());
    }

    /*
     * Tests
     */

    public function testToStringReturnsMessageName()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->get('https://fake');
        $this->assertEquals( (string) $msg, $msg->body->name);
    }

    /**
     * @testdox Valid status should return the translated string
     */
    public function testValidStatusReturnsString()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->get('https://fake');
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * @testdox Invalid status should return the given string
     */
    public function testInvalidStatusReturnsString()
    {
        $cli = $this->quickMock(['message_html_empty', '200']);
        $msg = $cli->messages->get('https://fake');
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertFalse(empty($res));
    }

    /**
     * @testdox create() returns a valid result
     *
     * @todo spy that client call get method on guzzle
     */
    public function testGet()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->get('https://fake');
        $this->checkMessage($msg);
        return $msg;
    }

    /**
     * @testdox put() returns a valid result
     */
    public function testPut()
    {
        $cli = $this->quickMock(
            ['message_ok', '200'],
            ['message_ok', '200']
        );
        $msg = $cli->messages->get('https://fake');
        $msg = $msg->put([]);

        $this->checkMessage($msg);
        $this->checkSentHistory('PUT');
    }

    /**
     */
    public function testDelete()
    {
        $cli = $this->quickMock(
            ['message_ok', '200'],
            ['empty', '204']
        );

        $msg = $cli->messages->get('https://fake');
        $msg = $msg->delete();

        $this->assertEmpty((array) $msg->body);

        $this->checkSentHistory('DELETE');
    }

    /**
     * @testdox Method hasBeenSent() works properly
     */
    public function testMessageHasBeenSent()
    {
        $cli = $this->quickMock(['message_sent', '200']);
        $msg = $cli->messages->get('https://fake');

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
        $cli = $this->quickMock(['message_sending', '200']);
        $msg = $cli->messages->get('https://fake');

        $this->assertTrue($msg->isSending($msg));

        $this->assertFalse($msg->hasBeenSent());
        $this->assertFalse($msg->hasIssue($msg));
        $this->assertFalse($msg->isReady($msg));
    }

    /**
     * @testdox Method isReady() works properly
     *
     * @depends testGet
     */
    public function testIsReady($msg)
    {
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
        $cli = $this->quickMock(['message_error', '200']);
        $msg = $cli->messages->get('https://fake');

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
        $cli = $this->quickMock(
            ['message_ok', '200'],
            ['message_sending', '200']
        );

        $msgBase = $cli->messages->get('https://fake');

        $msg = $msgBase->send();

        $this->assertFalse($msg === $msgBase);

        $this->checkSentHistory('PATCH');
    }
}
