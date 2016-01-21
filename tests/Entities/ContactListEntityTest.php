<?php
/**
 * Test class for Crunchmail\Entity\ContactListEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;
use Crunchmail\Entities\ContactListEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\ContactListEntity
 * @coversDefaultClass \Crunchmail\Entities\ContactListEntity
 */
class ContactListEntityTest extends TestCase
{

    /**
     * @covers ::get
     */
    public function testNestedResourcesReturnsANestedEntity()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $clist = $client->contacts->lists->get('http://fakeid');

        $this->assertEntity('ContactList', $clist);
    }

    /**
     * @covers ::merge
     * @todo verify enpoint
     */
    public function testMergeMethodReturnsAContactList()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');
        $clist  = new ContactListEntity($client->contacts->lists, $data);

        // ContactListEntity->merge->post

        $merge = $clist->merge(['fake', 'fake']);
        $this->assertEntity('ContactList', $merge);
    }

    // check sent values are the same ids
    /**
     * @covers ::merge
     */
    public function testMergeMethodWorksWithStrings()
    {
    }

    // check sent values are the same ids than entities ids
    /**
     * @covers ::merge
     */
    public function testMergeMethodWorksWithEntities()
    {
    }

    /**
     * @covers ::duplicate
     * @todo verify enpoint
     */
    public function testDuplicateMethodReturnsAContactList()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');
        $clist  = new ContactListEntity($client->contacts->lists, $data);

        $clone = $clist->duplicate();
        $this->assertEntity('ContactList', $clone);
    }

    /**
     * @covers ::addProperty
    public function testAddingPropertyWorks()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');
        $clist  = new ContactListEntity($client->contacts->lists, $data);

        $clist = $clist->addProperty('myprop', 'Boolean', true);

        // TODO: check request

        // TODO: check result
        $this->assertEntity('ContactList', $clist);
    }
     */

    /**
     * @covers ::editProperty
    public function testEditingPropertyWorks()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');
        $clist  = new ContactListEntity($client->contacts->lists, $data);

        $clist = $clist->editProperty('myprop', 'Boolean', true);

        // TODO: check request

        // TODO: check result
        $this->assertEntity('ContactList', $clist);
    }
     */

    /**
     * @covers ::deleteProperty
    public function testDeletingPropertyWorks()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');
        $clist  = new ContactListEntity($client->contacts->lists, $data);

        $clist->deleteProperty('myprop');

        // TODO: check request

    }
     */

}
