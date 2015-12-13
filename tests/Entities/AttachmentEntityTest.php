<?php
/**
 * Test class for Crunchmail\Entity\AttachmentEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\AttachmentEntity
 * @coversDefaultClass \Crunchmail\Entities\AttachmentEntity
 */
class AttachmentEntityTest extends TestCase
{

    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testAttachmentIsConvertedToString()
    {
        $client = $this->quickMock(
            ['attachment_ok', '200']
        );
        $attachment = $client->attachments->get('https://fake');

        $this->assertEquals($attachment->file, (string) $attachment);

        return $attachment;
    }

    /**
     * @depends testAttachmentIsConvertedToString
     * @covers ::__construct
     */
    public function testAttachmentAreProperlyFormed($attachment)
    {
        $this->assertObjectHasAttribute('file', $attachment->getBody());
        $this->assertInternalType('string', $attachment->getBody()->file);
    }
}
