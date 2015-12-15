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
use Crunchmail\Entities\AttachmentEntity;
use Crunchmail\PHPUnit\TestCase;

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
     */
    public function testAttachmentCanBeCreated()
    {
        $data = $this->getStdTemplate('file_ok');
        $cli  = $this->quickMock();

        $entity = new AttachmentEntity($cli->attachments, $data);

        $this->assertEntity('Attachment', $entity);

        return $entity;
    }

    /**
     * @depends testAttachmentCanBeCreated
     *
     * @covers ::__toString
     */
    public function testAttachmentIsConvertedToString($entity)
    {
        $this->assertEquals($entity->file, (string) $entity);
    }

    /**
     * @depends testAttachmentCanBeCreated
     *
     * @covers ::__construct
     */
    public function testAttachmentAreProperlyFormed($attachment)
    {
        $this->assertObjectHasAttribute('file', $attachment->getBody());
        $this->assertInternalType('string', $attachment->getBody()->file);
    }
}
