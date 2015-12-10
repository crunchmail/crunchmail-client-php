<?php
/**
 * Test class for Crunchmail\Resources\GenericResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Resource\GenericResource
 */
class GenericResourceTest extends \Crunchmail\Tests\TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testRelativeUrlThrowsAnException()
    {
        $client = $this->quickMock(['message_ok', '200']);
        $client->messages->get('invalid');
    }
}
