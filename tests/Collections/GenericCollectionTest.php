<?php
/**
 * Test class for Crunchmail\Collections\GenericCollection
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
 * @covers \Crunchmail\Collections\GenericCollection
 * @coversDefaultClass \Crunchmail\Collections\GenericCollection
 */
class GenericCollectionTest extends TestCase
{

    /* ---------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */
    public function directionProvider()
    {
        return [
            ['next', 'messages', 'messages_page_2'],
            ['previous', 'messages_page_2', 'messages']
        ];
    }

    /* ---------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * @covers ::current
     * @covers ::count
     * @covers ::pageCount
     *
     * @todo test generic collection
     */
    public function testCollectionCanBeRetrieveAsAnArray()
    {
        $client = $this->quickMock(['messages', 200]);

        $collection = $client->messages->get();

        var_dump($collection);

        $arr = $collection->current();

        $this->assertInternalType('array', $arr);

        $body = $this->getSentBody(0);
        $this->assertSame($body->count, $collection->count());
        $this->assertSame($body->page_count, $collection->pageCount());
    }

    /**
     * @covers ::previous
     * @covers ::next
     * @covers ::getAdjacent
     * @covers ::getResponse
     *
     * @dataProvider directionProvider
     */
    public function testRetrievingNextPage($direction, $msg1, $msg2)
    {
        $client = $this->quickMock(
            [$msg1, 200],
            [$msg2, 200]
        );
        $collection = $client->messages->get();

        $nextUrl = $collection->getResponse()->$direction;

        $collection->$direction();

        $request = $this->getHistoryRequest(1);

        $this->assertEquals($nextUrl, (string) $request->getUri());
    }

    /**
     * @covers ::previous
     * @covers ::next
     * @covers ::getAdjacent
     *
     * @dataProvider directionProvider
     */
    public function testRetrievingEmptyPageReturnsNull($direction)
    {
        $client = $this->quickMock(
            ['messages_one_page', 200],
            ['messages_one_page', 200]
        );
        $collection = $client->messages->get();
        $res = $collection->$direction();

        $this->assertNull($res);
    }

    /**
     * @covers ::refresh
     */
    public function testRefreshActuallyCallTheApi()
    {
        $client = $this->quickMock(
            ['messages', 200],
            ['messages_page_2', 200]
        );
        $collection = $client->messages->get();

        $res = $collection->refresh();

        $history = $this->getHistory();

        $this->assertCount(2, $history);
        $this->assertNotEquals($collection, $res);
    }
}
