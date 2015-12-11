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
    /**
     * File to test error about unreadable files
     * @var string
     */
    private $fileUnreadable;

    /* ---------------------------------------------------------------------
     * Pre/Post-run
     * --------------------------------------------------------------------- */

    /**
     * Before tests
     */
    protected function setUp()
    {
        // this file must be unreadable for tests
        $this->fileUnreadable = realpath(__DIR__ . '/../files/unreadable.svg');
        chmod($this->fileUnreadable, 0000);
    }

    /**
     * After tests
     */
    protected function tearDown()
    {
        // restore file permissions for git
        chmod($this->fileUnreadable, 0664);
    }

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

    public function addRecipients($to)
    {
        $client = $this->quickMock(
            ['message_ok'   , '200'],
            ['mail_push_ok' , '200']
        );
        $msg = $client->messages->get('http://fakeid');
        return $msg->addRecipients($to);
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

    public function addEmailProvider()
    {
        return [
            'empty_array' => [[]],
            'empty_email' => [''],
            'string_email' => ['fakeemail@fake.com'],
            'array_emails' => [['fakeemail@fake.com', 'another@fake.com']]
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
     * Test adding a proper recipient
     *
     * @covers ::add
     * @dataProvider addEmailProvider
     */
    public function testAddingValidEmailReturnsProperCount($to)
    {
        // string
        $res = $this->addRecipients($to);
        $this->assertEquals(1, $res->success_count);
    }

    /**
     * @covers ::add
     * @dataProvider addEmailProvider
     */
    public function testAddingRecipientSendExpectedParameters($to)
    {
        $this->addRecipients($to);
        $content = $this->getHistoryContent(1);

        $to = is_array($to) ? $to : [$to];

        $this->assertCount(count($to), $content);

        foreach ($content as $k => $row)
        {
            $this->assertEquals($to[$k], $row->to);
            $this->assertRegExp('#^https://.+/messages/[0-9]+/$#', $content[0]->message);
        }
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

    /**
     * @covers ::addAttachment
     * @covers \Crunchmail\Entities\AttachmentEntity::__toString
     */
    public function testAddingAFileWorksProperly()
    {
        $client = $this->quickMock(
            ['message_ok',    '200'],
            ['attachment_ok', '200']
        );
        $message = $client->messages->get('https://fake');

        $filepath= realpath(__DIR__ . '/../files/test.svg');
        $result = $message->addAttachment($filepath);

        $this->assertEquals($result->file, (string) $result);

        // checking result
        $this->assertInstanceOf('\Crunchmail\Entities\GenericEntity', $result);
        $this->assertObjectHasAttribute('body', $result);
        $this->assertObjectHasAttribute('file', $result->getBody());
        $this->assertInternalType('string', $result->getBody()->file);

        // checking request sent
        $content = $this->getHistoryContent(1, false);

        $this->assertContains('name="file"', $content);
        $this->assertContains('filename="test.svg"', $content);
        $this->assertContains('Content-Type: image/svg+xml', $content);
        $this->assertContains('Content-Length: 25354', $content);

        $req = $this->getHistoryRequest(1);
        $this->assertEquals('POST', $req->getMethod());
        $this->assertEquals('attachments/', (string) $req->getUri());
    }

    /**
     * @covers ::addAttachment
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testAddingAnExistingFileThrowsAnException()
    {
        $client = $this->quickMock(
            ['message_ok',    '200'],
            ['attachment_error', '400']
        );
        $message = $client->messages->get('https://fake');

        $filepath= realpath(__DIR__ . '/../files/test.svg');
        $result = $message->addAttachment($filepath);
    }

    /**
     * @covers ::addAttachment
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddingAMissingFileThrowsAnException()
    {
        $client = $this->quickMock(
            ['message_ok',    '200'],
            ['attachment_error', '400']
        );
        $message = $client->messages->get('https://fake');
        $result = $message->addAttachment('missing_file.svg');
    }

    /**
     * @covers ::addAttachment
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddingAnUnreadableFileThrowsAnException()
    {
        $client = $this->quickMock(
            ['message_ok',    '200'],
            ['attachment_error', '400']
        );
        $message = $client->messages->get('https://fake');
        $filepath=$this->fileUnreadable;

        // check file is not readable first
        if (!file_exists($filepath) || is_readable($filepath))
        {
           $this->markTestSkipped('The unreadable file is missing or is
             readable');
        }

        $result = $message->addAttachment($filepath);
    }

    /**
     * Test adding invalid recipients
     *
     * @covers ::addRecipients
     */
    public function testAddingInvalidEmailReturnsFailure()
    {
        $client = $this->quickMock(
            ['message_ok'      , '200'],
            ['mail_push_error' , '200']
        );

        $msg = $client->messages->get('http://fakeid');
        $res = $msg->addRecipients('error');

        $this->assertSame(0, $res->success_count);

        $invalid = (array) $res->failed;

        $this->assertInternalType('array', $invalid);
        $this->assertCount(1, $invalid);
    }

}
