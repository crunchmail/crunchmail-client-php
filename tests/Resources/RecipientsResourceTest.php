<?php
/**
 * Test class for Crunchmail\Resources\RecipientsResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once(__DIR__ . '/../helpers/cm_mock.php');

/**
 * Test class
 */
class RecipientsResourceTest extends PHPUnit_Framework_TestCase
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
     * @todo spy that client call post method on guzzle
     */
    public function testCreateReturnsAProperResult()
    {
        $client = cm_mock_client([[ 'message_ok', '200' ]]);
        $msg = $client->messages->post([]);
        $this->checkMessage($msg);
    }

    /**
     * Test adding invalid recipients
     */
    public function testAddingInvalidEmailReturnsFailure()
    {
        $client = cm_mock_client([
            ['message_ok'      , '200'],
            ['mail_push_error' , '200']
        ]);

        $msg = $client->messages->get('http://fakeid');
        $res = $msg->recipients->post('error');

        $this->assertEquals(0, $res->success_count);

        $invalid = (array) $res->failed;

        $this->assertInternalType('array', $invalid);
        $this->assertEquals(1, count($invalid));
    }

    /**
     * Test adding a proper recipient
     */
    public function testAddingValidEmailReturnsProperCount()
    {
        $client = cm_mock_client([
            ['message_ok'   , '200'],
            ['mail_push_ok' , '200']
        ]);
        $msg = $client->messages->get('http://fakeid');
        $res = $msg->recipients->post('fakeemail@domain.com');

        $this->assertEquals(1, $res->success_count);
    }

    /**
     * Test adding a proper recipient list
     *
     * @todo multiple values
     */
    public function testAddingValidEmailListReturnsProperCount()
    {
        $client = cm_mock_client([
            ['message_ok'   , '200'],
            ['mail_push_ok' , '200']
        ]);
        $msg = $client->messages->get('http://fakeid');
        $res = $msg->recipients->post(['fakeemail@domain.com']);

        $this->assertEquals(1, $res->success_count);
    }


}
