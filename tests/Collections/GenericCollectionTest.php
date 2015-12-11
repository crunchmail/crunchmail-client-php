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
 *
 * @covers \Crunchmail\Collections\GenericCollection
 * @coversDefaultClass \Crunchmail\Collections\GenericCollection
 */
class GenericCollectionTest extends \Crunchmail\Tests\TestCase
{

    /**
     * @covers ::current
     * @covers ::pageCount
     * @covers ::setCollection
     *
     * @todo test generic collection
     */
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
