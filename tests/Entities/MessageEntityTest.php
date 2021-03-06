<?php
/**
 * Test class for Crunchmail\Entity\MessageEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;
use Crunchmail\Entities\MessageEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\MessageEntity
 * @coversDefaultClass \Crunchmail\Entities\MessageEntity
 */
class MessageEntityTest extends TestCase
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

    /**
     * @todo move to assertMessageEntity
     */
    public function checkMessage($msg)
    {
        $this->assertEntity('Message', $msg);
        $this->assertObjectNotHasAttribute('_links', $msg->getBody());
        $this->assertInternalType('boolean', $msg->getBody()->track_clicks);
        $this->assertEquals('message_ok', $msg->getBody()->status);
    }

    public function checkSentHistory($method, $ind = 1, $reg = '#.*/messages/[0-9]+/$#')
    {
        $req = $this->getHistoryRequest($ind);
        $this->assertEquals($method, $req->getMethod());
        $this->assertRegExp($reg, (string) $req->getUri());
    }

    public function addRecipients($recipients)
    {
        $client = $this->quickMock(
            ['message_ok'   , '200'],
            ['mail_push_ok' , '200']
        );
        $msg = $client->messages->get('http://fakeid');
        return $msg->addRecipients($recipients);
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
            ['preview.txt']
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
     * @covers ::__construct
     */
    public function testMessageCanBeCreated()
    {
        $data = $this->getStdTemplate('message_ok');
        $cli  = $this->quickMock();
        $entity = new MessageEntity($cli->messages, $data);

        $this->assertEntity('Message', $entity);

        return $entity;
    }

    /**
     * @depends testMessageCanBeCreated
     *
     */
    public function testMessageIsConvertedToString($entity)
    {
        $this->assertEquals($entity->name, (string) $entity);
    }

    /**
     * Test adding a proper recipient
     *
     * @covers ::addRecipients
     * @dataProvider addEmailProvider
     */
    public function testAddingValidEmailReturnsProperCount($recipients)
    {
        // string
        $res = $this->addRecipients($recipients);
        $this->assertEquals(1, $res->success_count);
        $this->assertEntity('Recipient', $res);
    }

    /**
     * @covers ::addRecipients
     * @dataProvider addEmailProvider
     */
    public function testAddingRecipientSendExpectedParameters($recipients)
    {
        $this->addRecipients($recipients);
        $content = $this->getHistoryContent(1);

        $recipients = is_array($recipients) ? $recipients : [$recipients];

        $this->assertCount(count($recipients), $content);

        foreach ($content as $k => $row)
        {
            $this->assertEquals($recipients[$k], $row->to);
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

    /**
     * @depends testGet
     * @dataProvider resourcesPathProvider
     *
     * @covers ::__get
     */
    public function testAccessingResourceWorksProperly($path, $msg)
    {
        $this->assertGenericResource($msg->$path);
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

        $this->assertNull($msg->getBody());

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
     * @covers ::__get
     * @depends testGet
     */
    public function testAccessingArchiveUrlWorks($msg)
    {
        $this->assertContains('hosted', $msg->archive_url);
    }

    /**
     * @covers ::addAttachment
     */
    public function testAddingAFile()
    {
        $cli = $this->quickMock(
            ['attachment_ok', '200']
        );

        $data = $this->getStdTemplate('message_ok');
        $msg = new MessageEntity($cli->messages, $data);

        $filepath= realpath(__DIR__ . '/../files/test.svg');
        $attachment = $msg->addAttachment($filepath);

        // checking request sent
        $content = $this->getHistoryContent(0, false);

        $this->assertContains('name="file"', $content);
        $this->assertContains('filename="test.svg"', $content);
        $this->assertContains('Content-Type: image/svg+xml', $content);
        $this->assertContains('Content-Length: 25354', $content);

        $req = $this->getHistoryRequest(0);
        $this->assertEquals('POST', $req->getMethod());
        $this->assertEquals('attachments/', (string) $req->getUri());

        return $attachment;
    }

    /**
     * @depends testAddingAFile
     *
     * @covers ::addAttachment
     */
    public function testAddingAttachmentReturnsAProperEntity($attachment)
    {
        // checking attachment
        $this->assertGenericEntity($attachment);
    }

    /**
     * @covers ::addAttachment
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testAddingAnExistingFileThrowsAnException()
    {
        $cli = $this->quickMock(
            ['attachment_error', '400']
        );

        $data = $this->getStdTemplate('message_ok');
        $msg = new MessageEntity($cli->messages, $data);

        $filepath= realpath(__DIR__ . '/../files/test.svg');
        $msg->addAttachment($filepath);
    }

    /**
     * @covers ::addAttachment
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddingAMissingFileThrowsAnException()
    {
        $cli = $this->quickMock(
            ['attachment_error', '400']
        );

        $data = $this->getStdTemplate('message_ok');
        $msg = new MessageEntity($cli->messages, $data);

        $msg->addAttachment('missing_file.svg');
    }

    /**
     * @covers ::addAttachment
     *
     * @depends testMessageCanBeCreated
      *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddingAnUnreadableFileThrowsAnException($msg)
    {
        $client = $this->quickMock(
            ['attachment_error', '400']
        );
        $filepath=$this->fileUnreadable;

        // check file is not readable first
        if (!file_exists($filepath) || is_readable($filepath))
        {
            $this->markTestSkipped('The unreadable file is missing or readable');
        }

        $msg->addAttachment($filepath);
    }

    /**
     * Test adding invalid recipients
     *
     * @covers ::addRecipients
     */
    public function testAddingInvalidEmailReturnsFailure()
    {
        $cli = $this->quickMock(
            ['mail_push_error' , '200']
        );

        $data = $this->getStdTemplate('message_ok');
        $msg  = new MessageEntity($cli->messages, $data);

        $res = $msg->addRecipients('error');

        $this->assertSame(0, $res->success_count);

        $invalid = (array) $res->failed;

        $this->assertInternalType('array', $invalid);
        $this->assertCount(1, $invalid);
    }

    /**
     * @covers ::previewSend
     */
    public function testPreviewSend()
    {
        $cli = $this->quickMock(
            ['empty' , '200']
        );

        $data   = $this->getStdTemplate('message_ok');
        $entity = new MessageEntity($cli->messages, $data);

        $email = 'toto@fake.com';
        $res = $entity->previewSend($email);

        $this->assertGenericEntity($res);
    }
}
