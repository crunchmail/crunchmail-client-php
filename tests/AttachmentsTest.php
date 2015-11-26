<?php
/**
 * Test class for Crunchmail\Attachments
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once('helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Attachments
 */
class AttachmentsTest extends PHPUnit_Framework_TestCase
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
        $this->fileUnreadable = realpath(__DIR__ . '/files/unreadable.svg');
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
     */
    public function testAddingAFileReturnsAProperResult()
    {
        $client = cm_mock_client(200, 'file_ok');
        $filepath= realpath(__DIR__ . '/files/test.svg');
        $result = $client->attachments->upload('fakeid', $filepath);

        $this->assertInstanceOf('stdClass', $result);
        $this->assertInternalType('string', $result->file);
    }

    /**
     * @covers ::upload
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testAddingAnExistingFileThrowsAnException()
    {
        $client = cm_mock_client(400, 'file_error');
        $filepath= realpath(__DIR__ . '/files/test.svg');
        $result = $client->attachments->upload('fakeid', $filepath);
    }

    /**
     * @covers ::upload
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddingAMissingFileThrowsAnException()
    {
        $client = cm_mock_client(200, 'empty');
        $filepath='missing_file.svg';
        $result = $client->attachments->upload('fakeid', $filepath);
    }

    /**
     * @covers ::upload
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddingAnUnreadableFileThrowsAnException()
    {
        $client = cm_mock_client(200, 'empty');
        $filepath=$this->fileUnreadable;

        // check file is not readable first
        if (!file_exists($filepath) || is_readable($filepath))
        {
           $this->markTestSkipped('The unreadable file is missing or is
             readable');
        }

        $result = $client->attachments->upload('fakeid', $filepath);
    }
}
