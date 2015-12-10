<?php
/**
 * Test class for Crunchmail\Resources\MessagesResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once(__DIR__ . '/../helpers/cm_mock.php');

/**
 * Test class
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
     * @testdox Method post() throws an exception on invalid domain
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testCreateWithInvalidDomain()
    {
        $client = cm_mock_client([['domains_invalid_mx', '400']]);
        $result = $client->messages->post([]);
    }


}
