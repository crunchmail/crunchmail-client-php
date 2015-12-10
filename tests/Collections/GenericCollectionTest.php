<?php
/**
 * Test class for Crunchmail\Collections\GenericCollection
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 */
class GenericCollectionTest extends \Crunchmail\Tests\TestCase
{
    public function testCollectionCanBeFiltered()
    {
        $client = $this->quickMock(['messages', 200]);

        $collection = $client->messages->filter(['search' => 'fake'])->get();
        $arr = $collection->current();

        $history = $this->getHistory();
        $req = $history[0]['request'];

        $this->assertEquals('search=fake', $req->getUri()->getQuery());
    }

    public function testCollectionCanBeRetrieveAsAnArray()
    {
        $client = $this->quickMock(['messages', 200]);

        $collection = $client->messages->get();

        $arr = $collection->current();

        $this->assertInternalType('array', $arr);

        $body = $this->getSentBody(0);
        $this->assertSame($body->count, $collection->count());
        $this->assertSame($body->page_count, $collection->pageCount());
        $this->assertContainsOnlyInstancesOf(
            '\Crunchmail\Entities\MessageEntity', $arr);
    }

}
