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
use Crunchmail\Entities\ContactListEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\ContactEntity
 * @coversDefaultClass \Crunchmail\Entities\ContactEntity
 */
class ContactEntityTest extends TestCase
{
    /**
     * @covers ::copyTo
     */
    public function testCopyMethodReturnsAContact()
    {
        $client = $this->quickMock(['contact_ok' , '200']);

        $data   = $this->getStdTemplate('contact_ok');

        $dataList = new \stdClass();
        $dataList->url = 'fakeurl';

        $contact = new ContactEntity($client->contacts, $data);
        $list    = new ContactListEntity($client->contacts->lists, $dataList);

        // call to test
        $clone = $contact->copyTo($list);

        // check result
        $this->assertEntity('Contact', $clone);

        // check request history
        $content = $this->getHistoryContent(0);
        $this->assertEquals('fakeurl', $content->contact_list);

        $this->assertEntity('Contact', $clone);
    }
}
