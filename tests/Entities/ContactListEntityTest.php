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
     * @covers ::__get
     */
    public function testNestedResourcesReturnsANestedEntity()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $clist = $client->contacts->lists->get('http://fakeid');

        $this->assertEntity('ContactList', $clist);
    }

    /**
     * @covers ::merge
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

    /**
     * @covers ::merge
     */
    public function testMergeMethodWorksWithEntities()
    {
        $client = $this->quickMock(['contact_list_ok' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');
        $clist  = new ContactListEntity($client->contacts->lists, $data);

        $data2 = clone $data;
        $data3 = clone $data;

        $data2->url = 'fake2';
        $data3->url = 'fake3';

        // ContactListEntity->merge->post
        $list = [
            new ContactListEntity($client->contacts->lists, $data2),
            new ContactListEntity($client->contacts->lists, $data3)
        ];

        $merge = $clist->merge($list);

        $content = $this->getHistoryContent(0);

        $this->assertEquals('fake2', $content[0]);
        $this->assertEquals('fake3', $content[1]);

        $this->assertEntity('ContactList', $merge);
    }

    /**
     * @covers ::import
     */
    public function testCsvImport()
    {
        $client = $this->quickMock(['contacts' , '200']);
        $data   = $this->getStdTemplate('contact_list_ok');

        $clist  = new ContactListEntity($client->contacts->lists, $data);

        $content = $this->getTemplate('import.csv');
        $result = $clist->import($content);

        $this->assertGenericCollection($result);

        $history = $this->getHistoryContent(0);
        $request = $this->getHistoryRequest(0);

        // check that content is sent as raw data
        $this->assertEquals($content, $history);

        $this->assertEquals('contacts/', $request->getUri()->getPath());
        $this->assertEquals('text/csv', $request->getHeaders()['Content-Type'][0]);
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
