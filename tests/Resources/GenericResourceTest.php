<?php
/**
 * Test class for Crunchmail\Resources\GenericResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once(__DIR__ . '/../helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Resource\GenericResource
 */
class GenericResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testRelativeUrlThrowsAnException()
    {
        $client = cm_mock_client([['message_ok', '200']]);
        $client->messages->get('invalid');
    }
}
