<?php
/**
 * Test class for Crunchmail\Entity\RecipientEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;
use Crunchmail\Entities\RecipientEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\RecipientEntity
 * @coversDefaultClass \Crunchmail\Entities\RecipientEntity
 */
class RecipientEntityTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testRecipientCanBeCreated()
    {
        $data = $this->getStdTemplate('mail_ok');
        $cli  = $this->quickMock();

        $entity = new RecipientEntity($cli->recipients, $data);

        $this->assertEntity('Recipient', $entity);

        return $entity;
    }

    /**
     * @depends testRecipientCanBeCreated
     *
     * @covers ::__toString
     */
    public function testRecipientIsConvertedToString($entity)
    {
        $this->assertEquals($entity->to, (string) $entity);
    }
}
