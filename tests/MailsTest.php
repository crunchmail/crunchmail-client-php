<?php
/**
 * Test class for Crunchmail\Mails
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Mails
 */
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

require_once('helpers/cm_mock.php');

/**
 * Test class
 */
class MailsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test adding mail to an invalid message
     *
     * @todo verify API is working (404)
     *
     * @covers \Crunchmail\Mails::push
     */
    /*
    public function testInvalidPush()
    {
        // Create a mock and queue two responses.
        $tpl = 'mails_push_error';
        $body = file_get_contents(__DIR__ . '/responses/' . $tpl . '.json');

        // Create a mock and queue two responses.
        $mock = new MockHandler([ new Response('200', [], $body) ]);

        $handler = HandlerStack::create($mock);
        $client = new Crunchmail\Client(['base_uri' => '', 'handler' =>
            $handler]);

        $res = $client->mails->push('fakeid', 'fakeemail@domain.com');
    }
     */

    /**
     * Test adding invalid recipients
     *
     * @covers \Crunchmail\Mails::push
     */
    public function testAddingInvalidEmailReturnsFailure()
    {
        // Create a mock and queue two responses.
        $client = cm_mock_client(200, 'mail_push_error');
        $res = $client->mails->push('fakeid', 'invalid');

        $this->assertEquals(0, $res->success_count);

        $invalid = (array) $res->failed;

        $this->assertInternalType('array', $invalid);
        $this->assertEquals(1, count($invalid));
    }

    /**
     * Test adding a proper recipient
     *
     * @covers \Crunchmail\Mails::push
     */
    public function testAddingValidEmailReturnsProperCount()
    {
        // Create a mock and queue two responses.
        $client = cm_mock_client(200, 'mails_push_ok');
        $res = $client->mails->push('fakeid', 'fakeemail@domain.com');

        $this->assertEquals(1, $res->success_count);
    }

    /**
     * Test adding a proper recipient list
     *
     * @covers \Crunchmail\Mails::push
     */
    public function testAddingValidEmailListReturnsProperCount()
    {
        $client = cm_mock_client(200, 'mails_push_ok');

        $list = [ 'anemail@test.com' ];
        $res = $client->mails->push('fakeid', 'fakeemail@domain.com');

        $this->assertEquals(1, $res->success_count);
    }

}
