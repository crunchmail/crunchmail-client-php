<?php
/**
 * Test class for Crunchmail\Resources\AttachmentsResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once('helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Resource\AttachmentsResource
 */
class AttachementsResourceTest extends PHPUnit_Framework_TestCase
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
     * @covers ::upload
     * @todo mulitpart check
     */
    public function testAddingAFileReturnsAProperResult()
    {
        $message = cm_get_message(['message_ok' => '200', 'attachment_ok' => '200']);

        $filepath= realpath(__DIR__ . '/../files/test.svg');
        $result = $message->attachments->upload($filepath);

        $this->assertInstanceOf('\Crunchmail\Entities\GenericEntity', $result);
        $this->assertObjectHasAttribute('body', $result);
        $this->assertObjectHasAttribute('file', $result->body);
        $this->assertInternalType('string', $result->body->file);
    }

    /**
     * @covers ::upload
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testAddingAnExistingFileThrowsAnException()
    {
        $message = cm_get_message(['message_ok' => '200', 'attachment_error' => '400']);
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
        $message = cm_get_message(['message_ok' => '200', 'attachment_error' => '400']);
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
        $message = cm_get_message(['message_ok' => '200', 'attachment_error' => '400']);
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
