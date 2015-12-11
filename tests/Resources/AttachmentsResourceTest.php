<?php
/**
 * Test class for Crunchmail\Resources\AttachmentsResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 *
 * @covers \Crunchmail\Resources\AttachmentsResource
 * @coversDefaultClass \Crunchmail\Resources\AttachmentsResource
 */
class AttachementsResourceTest extends \Crunchmail\Tests\TestCase
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
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * @covers ::upload
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
        $result = $message->attachments->upload($filepath);

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
     * @covers ::upload
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
        $result = $message->attachments->upload($filepath);
    }

    /**
     * @covers ::upload
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
        $result = $message->attachments->upload('missing_file.svg');
    }

    /**
     * @covers ::upload
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

        $result = $message->attachments->upload($filepath);
    }
}
