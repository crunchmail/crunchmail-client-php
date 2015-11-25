<?php
/**
 * Test class for Crunchmail\Attachments
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Attachments
 */
require_once('helpers/cm_mock.php');

/**
 * Test class
 */
class AttachmentsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check adding a file
     *
     * @covers \Crunchmail\Attachments::join
     */
    public function testAddingAFileReturnsAProperResult()
    {
        $client = cm_mock_client(200, 'file_ok');
        $filepath= realpath(__DIR__ . '/files/test.svg');
        $result = $client->attachments->join('fakeid', $filepath);

        $this->assertInstanceOf('stdClass', $result);
        $this->assertInternalType('string', $result->file);
    }

    /**
     * Check adding a file that already exists
     *
     * @covers \Crunchmail\Attachments::join
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testAddingAnExistingFileThrowsAnException()
    {
        $client = cm_mock_client(400, 'file_error');
        $filepath= realpath(__DIR__ . '/files/test.svg');
        $result = $client->attachments->join('fakeid', $filepath);
    }

    /**
     * Check adding a missing file
     *
     * @covers \Crunchmail\Attachments::join
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testAddingAMissingFileThrowsAnException()
    {
        $client = cm_mock_client(200, 'empty');
        $filepath='missing_file.svg';
        $result = $client->attachments->join('fakeid', $filepath);
    }

    /**
     * Check adding an unreadable file
     *
     * @covers \Crunchmail\Attachments::join
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testAddingAUnreadableFileThrowsAnException()
    {
        $client = cm_mock_client(200, 'empty');
        $filepath=realpath(__DIR__ . '/files/unreadable.svg');

        // check file is not readable first
        if (!file_exists($filepath) || is_readable($filepath))
        {
           $this->markTestSkipped('The unreadable file is missing or is
             readable');
        }

        $result = $client->attachments->join('fakeid', $filepath);
    }
}
