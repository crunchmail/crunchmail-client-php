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
 */
class AttachementsResourceTest extends \Crunchmail\Tests\TestCase
{
    /**
     * File to test error about unreadable files
     * @var string
     */
    private $fileUnreadable;

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

    /**
     * @todo mulitpart check
     */
    public function testAddingAFileReturnsAProperResult()
    {
        $client = $this->quickMock(
            ['message_ok',    '200'],
            ['attachment_ok', '200']
        );
        $message = $client->messages->get('https://fake');

        $filepath= realpath(__DIR__ . '/../files/test.svg');
        $result = $message->attachments->upload($filepath);

        $this->assertInstanceOf('\Crunchmail\Entities\GenericEntity', $result);
        $this->assertObjectHasAttribute('body', $result);
        $this->assertObjectHasAttribute('file', $result->body);
        $this->assertInternalType('string', $result->body->file);
    }

    /**
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
