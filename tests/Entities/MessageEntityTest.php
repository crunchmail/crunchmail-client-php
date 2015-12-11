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
 *
 * @covers \Crunchmail\Entities\MessageEntity
 * @coversDefaultClass \Crunchmail\Entities\MessageEntity
 */
class MessageEntityTest extends \Crunchmail\Tests\TestCase
{
    /* ---------------------------------------------------------------------
     * Helpers
     * --------------------------------------------------------------------- */

    public function checkMessage($msg)
    {
        $this->assertInstanceOf('\Crunchmail\Entities\MessageEntity', $msg);
        $this->assertObjectHasAttribute('_links', $msg->getBody());
        $this->assertInternalType('boolean', $msg->getBody()->track_clicks);
        $this->assertEquals('message_ok', $msg->getBody()->status);
    }

    public function checkSentHistory($method, $i=1, $reg='#.*/messages/[0-9]+/$#')
    {
        $req = $this->getHistoryRequest(1);
        $this->assertEquals($method, $req->getMethod());
        $this->assertRegExp($reg, (string) $req->getUri());
    }

    /* ---------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */

    public function resourcesPathProvider()
    {
        return [
            ['attachments'],
            ['recipients'],
            ['stats'],
            ['bounces']
        ];
    }

    public function blacklistedResourcesProvider()
    {
        return [
            ['preview.html'],
            ['preview.txt'],
            ['archive_url'],
            ['opt_outs'],
            ['spam_details']
        ];
    }


    /* ---------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * @covers ::__get
     */
    public function testToStringReturnsMessageName()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->get('https://fake');
        $this->assertEquals( (string) $msg, $msg->getBody()->name);
    }

    /**
     * @testdox Valid status should return the translated string
     * @covers ::readableStatus
     */
    public function testValidStatusReturnsString()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->get('https://fake');
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertNotEmpty($res);
    }

    /**
     * @testdox Invalid status should return the given string
     * @covers ::readableStatus
     */
    public function testInvalidStatusReturnsString()
    {
        $cli = $this->quickMock(['message_html_empty', '200']);
        $msg = $cli->messages->get('https://fake');
        $res = $msg->readableStatus();
        $this->assertInternalType('string', $res);
        $this->assertNotEmpty($res);
    }

    /**
     * @testdox create() returns a valid result
     *
     * @covers ::__construct
     */
    public function testGet()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->get('https://fake');
        $this->checkMessage($msg);
        return $msg;
    }

    public function retrievePreview($method)
    {
        $cli = $this->quickMock(
            ['message_ok', '200'],
            ['preview', '200']
        );
        $msg = $cli->messages->get('https://fake');
        return $msg->$method();
    }

    /**
     * @depends testGet
     * @covers ::html
     */
    public function testRetrievingHtmlContent($msg)
    {
        $result = $this->retrievePreview('html');
        $this->assertStringStartsWith('<!DOCTYPE html', (string) $result);
    }

    /**
     * @depends testGet
     * @covers ::txt
     */
    public function testRetrievingTxtContent($msg)
    {
        $result = $this->retrievePreview('txt');

        $this->assertNotContains('DOCTYPE', (string) $result);
        $this->assertContains('UNSUBSCRIBE', (string) $result);
    }

    /**
     * @depends testGet
     * @dataProvider resourcesPathProvider
     *
     * @covers ::__get
     */
    public function testAccessingResourceWorksProperly($path, $msg)
    {
        $resource = $msg->$path;

        $this->assertInstanceOf('\Crunchmail\Resources\GenericResource',
            $resource);
    }

    /**
     * @testdox put() returns a valid result
     *
     * @covers ::__call
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
     * @covers ::__call
     */
    public function testDelete()
    {
        $cli = $this->quickMock(
            ['message_ok', '200'],
            ['empty', '204']
        );

        $msg = $cli->messages->get('https://fake');
        $msg = $msg->delete();

        $this->assertEmpty((array) $msg->getBody());

        $this->checkSentHistory('DELETE');
    }

    /**
     * @testdox Method hasBeenSent() works properly
     *
     * @covers ::hasBeenSent
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
     *
     * @covers ::isSending
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
     * @covers ::isReady
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
     *
     * @covers ::hasIssue
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
     *
     * @covers ::send
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

    /**
     * @covers ::__get
     * @depends testGet
     * @dataProvider blacklistedResourcesProvider
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testBlacklistedResourcesAreNotReachable($field, $msg)
    {
        $msg->$field;
    }

}
