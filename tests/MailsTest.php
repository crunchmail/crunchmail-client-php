<?php
/**
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Mails
 */
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

class MailsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test adding mail to an invalid message
     *
     * @todo verify API is working (404)
     *
     * @covers ::push
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
     * Test adding mail to an invalid message
     *
     * @covers ::push
     */
    public function testInvalidEmail()
    {
    }

    /**
     * Test adding mail to an invalid message
     * @todo test result type
     *
     * @covers ::push
     */
    public function testPush()
    {
        // Create a mock and queue two responses.
        $tpl = 'mails_push_ok';
        $body = file_get_contents(__DIR__ . '/responses/' . $tpl . '.json');

        // Create a mock and queue two responses.
        $mock = new MockHandler([ new Response('200', [], $body) ]);

        $handler = HandlerStack::create($mock);
        $client = new Crunchmail\Client(['base_uri' => '', 'handler' =>
            $handler]);

        $res = $client->mails->push('fakeid', 'fakeemail@domain.com');
    }

}
