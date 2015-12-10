<?php
/**
 * Test class for Crunchmail\Messages
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
require_once('helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Messages
 */
class MessagesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Method create() throws an exception on invalid domain
     *
     * @covers ::create
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testCreateWithInvalidDomain()
    {
        $client = cm_mock_client(400, 'domains_invalid_mx');
        $result = $client->messages->create([]);
    }

    /**
     * @covers ::sendMessage
     */
    public function testSendingAMessageReturnsAValidResponse()
    {
        $container = [];
        $client = cm_mock_client(200, ['message_sending'], $container);

        $res = $client->messages->sendMessage('https://testid');

        $this->assertInstanceOf('stdClass', $res);
        $this->assertTrue(\Crunchmail\Messages::isSending($res));

        $req = $container[0]['request'];
        $this->assertEquals(1, count($container));
        $this->assertEquals('PATCH', $req->getMethod());
        $this->assertEquals('https://testid', (string) $req->getUri());
    }

    /**
     * @covers ::sendPreview
     *
     * @expectedExceptionCode 500
     * @expectedException Crunchmail\Exception\ApiException
     */
     public function testGetPreviewUrlError()
     {
        $client = cm_mock_client(500, ['message_error', 'message_error']);
        $res = $client->messages->sendPreview('https://testid', 'f@fake.fr');
     }

}
