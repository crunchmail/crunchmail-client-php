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

    public function addRecipient($to)
    {
        $client = $this->quickMock(
            ['message_ok'   , '200'],
            ['mail_push_ok' , '200']
        );
        $msg = $client->messages->get('http://fakeid');
        return $msg->recipients->add($to);
    }

    /* ---------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */

    public function addEmailProvider()
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
     * @covers ::add
     */
    public function testAddingInvalidEmailReturnsFailure()
    {
        $client = $this->quickMock(
            ['message_ok'      , '200'],
            ['mail_push_error' , '200']
        );

        $msg = $client->messages->get('http://fakeid');
        $res = $msg->recipients->add('error');

        $this->assertSame(0, $res->success_count);

        $invalid = (array) $res->failed;

        $this->assertInternalType('array', $invalid);
        $this->assertCount(1, $invalid);
    }

    /**
     * Test adding a proper recipient
     *
     * @covers ::add
     * @dataProvider addEmailProvider
     */
    public function testAddingValidEmailReturnsProperCount($to)
    {
        // string
        $res = $this->addRecipient($to);
        $this->assertEquals(1, $res->success_count);
    }

    /**
     * @covers ::add
     * @dataProvider addEmailProvider
     */
    public function testAddingRecipientSendExpectedParameters($to)
    {
        $this->addRecipient($to);
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
     * @covers ::add
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAddMethodIsForbiddenOutOfContext()
    {
        $client = $this->quickMock(['message_ok'   , '200']);
        $client->recipients->add('whatever');
    }


}
