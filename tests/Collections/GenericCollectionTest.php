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
    public function testCurrentReturnsProperValues()
    {
        $client = $this->quickMock(['messages', 200]);

        $collection = $client->messages->get();

        $arr = $collection->current();

        $this->assertInternalType('array', $arr);

        $body = $this->getSentBody(0);
        $this->assertEquals($body->count, $collection->count());
    }

}
