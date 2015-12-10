<?php
/**
 * Test class for Crunchmail\Entity\RecipientEntity
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

require_once(__DIR__ . '/..//helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Mails
 */
class RecipientEntityTest extends PHPUnit_Framework_TestCase
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

}
