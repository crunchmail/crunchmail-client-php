<?php
/**
 * Test class for Crunchmail\Resources\MessagesResource
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Crunchmail\Tests;

/**
 * Test class
 */
class MessagesResourceTest extends TestCase
{
    /**
     * Helpers
     */
    public function checkMessage($msg)
    {
        $this->assertEntity($msg, 'Message');
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
        $client = $this->quickMock(['domains_invalid_mx', '400']);
        $client->messages->post([]);
    }
}
