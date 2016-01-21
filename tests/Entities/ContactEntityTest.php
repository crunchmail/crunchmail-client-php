<?php
/**
 * Test class for Crunchmail\Entity\ContactEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;
use Crunchmail\Entities\ContactEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\ContactEntity
 * @coversDefaultClass \Crunchmail\Entities\ContactEntity
 */
class ContactEntityTest extends TestCase
{
    public function testThisIsEmpty()
    {
    }

    /**
     * @covers ::clone
     * @todo verify enpoint
    public function testCloneMethodReturnsAContact()
    {
        $client  = $this->quickMock(['contact_ok' , '200']);
        $data    = $this->getStdTemplate('contact_ok');
        $contact = new ContactEntity($client->contacts, $data);

        $clone = $contact->copyTo('fake');
        $this->assertEntity('Contact', $clone);
    }
     */
}
