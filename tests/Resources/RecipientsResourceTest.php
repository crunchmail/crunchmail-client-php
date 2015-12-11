<?php
/**
 * Test class for Crunchmail\Resources\RecipientsResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 *
 * @covers \Crunchmail\Resources\RecipientsResource
 * @coversDefaultClass \Crunchmail\Resources\RecipientsResource
 */
class RecipientsResourceTest extends \Crunchmail\Tests\TestCase
{
    /* ---------------------------------------------------------------------
     * Helpers
     * --------------------------------------------------------------------- */
    public function checkMessage($msg)
    {
        $this->assertInstanceOf('\Crunchmail\Entities\MessageEntity', $msg);
        $this->assertObjectHasAttribute('body', $msg);
        $this->assertObjectHasAttribute('_links', $msg->body);
        $this->assertInternalType('boolean', $msg->body->track_clicks);
        $this->assertEquals('message_ok', $msg->body->status);
    }

    public function postRecipient($to)
    {
        $client = $this->quickMock(
            ['message_ok'   , '200'],
            ['mail_push_ok' , '200']
        );
        $msg = $client->messages->get('http://fakeid');
        return $msg->recipients->post($to);
    }

    /* ---------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */

    public function postEmailProvider()
    {
        return [
            'empty_array' => [[]],
            'empty_email' => [''],
            'string_email' => ['fakeemail@fake.com'],
            'array_emails' => [['fakeemail@fake.com', 'another@fake.com']]
        ];
    }

    /* ---------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * Test adding invalid recipients
     *
     * @covers ::post
     */
    public function testAddingInvalidEmailReturnsFailure()
    {
        $client = $this->quickMock(
            ['message_ok'      , '200'],
            ['mail_push_error' , '200']
        );

        $msg = $client->messages->get('http://fakeid');
        $res = $msg->recipients->post('error');

        $this->assertSame(0, $res->success_count);

        $invalid = (array) $res->failed;

        $this->assertInternalType('array', $invalid);
        $this->assertCount(1, $invalid);
    }

    /**
     * Test adding a proper recipient
     *
     * @covers ::post
     * @dataProvider postEmailProvider
     */
    public function testAddingValidEmailReturnsProperCount($to)
    {
        // string
        $res = $this->postRecipient($to);
        $this->assertEquals(1, $res->success_count);
    }

    /**
     * @covers ::post
     * @dataProvider postEmailProvider
     */
    public function testAddingRecipientSendExpectedParameters($to)
    {
        $this->postRecipient($to);
        $content = $this->getHistoryContent(1);

        $to = is_array($to) ? $to : [$to];

        $this->assertCount(count($to), $content);

        foreach ($content as $k => $row)
        {
            $this->assertEquals($to[$k], $row->to);
            $this->assertRegExp('#^https://.+/messages/[0-9]+/$#', $content[0]->message);
        }
    }

    /**
     * @covers ::post
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testPostMethodIsForbiddenOutOfContext()
    {
        $client = $this->quickMock(['message_ok'   , '200']);
        $client->recipients->post('whatever');
    }


}
