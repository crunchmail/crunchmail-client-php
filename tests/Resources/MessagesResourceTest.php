<?php
/**
 * Test class for Crunchmail\Resources\MessagesResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once('helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Resources\MessagesResource
 */
class MessagesResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Helpers
     */
    public function checkMessage($msg)
    {
        $this->assertInstanceOf('\Crunchmail\Entities\MessageEntity', $msg);
        $this->assertObjectHasAttribute('body', $msg);
        $this->assertObjectHasAttribute('_links', $msg->body);
        $this->assertInternalType('boolean', $msg->body->track_clicks);
        $this->assertEquals('message_ok', $msg->body->status);
    }

    /**
     * @testdox create() returns a valid result
     *
     * @covers ::create
     * @covers ::apiRequest
     *
     * @todo spy that client call post method on guzzle
     */
    public function testCreateReturnsAProperResult()
    {
        $client = cm_mock_client([ 'message_ok' => '200' ]);
        $msg = $client->messages->post([]);
        $this->checkMessage($msg);
    }

}
