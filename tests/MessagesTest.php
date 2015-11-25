<?php
/**
 * Test class for Crunchmail\Messages
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Messages
 */
require_once('helpers/cm_mock.php');

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

class MessagesTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    /**
     * Create a mocked client and execute requested method
     *
     * @param string $method    method to test
     * @param string $tpl       template name
     * @param string $param     method param
     * @param int    $code      http status code of response
     */
    protected function prepareCheck($method, $tpl, $param, $code=200)
    {
    }

    /**
     * @covers ::hasBeenSent
     */
    public function testMessageHasBeenSent()
    {
        $msg = cm_get_message('message_sent');
        $this->assertTrue(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
        $this->assertFalse(Crunchmail\Messages::isReady($msg));
    }

    /**
     * @covers ::isSending
     */
    public function testMessageIsSending()
    {
        $msg = cm_get_message('message_sending');
        $this->assertTrue(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::isReady($msg));
    }

    /**
     * @covers ::isReady
     */
    public function testMessageIsReady()
    {
        $msg = cm_get_message('message_ok');
        $this->assertTrue(Crunchmail\Messages::isReady($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
    }

    /**
     * @covers ::hasIssue
     */
    public function testMessageHasError()
    {
        $msg = cm_get_message('message_error');
        $this->assertTrue(Crunchmail\Messages::hasIssue($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::isReady($msg));
    }

    /**
     * @covers ::create
     */
    public function testCreateAMessage()
    {
        $client = cm_mock_client(200, 'message_ok');
        $result = $client->messages->create([]);

        $this->assertEquals('message_ok', $result->status);
    }

    /**
     * @covers ::create
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testCreateWithInvalidDomain()
    {
        $client = cm_mock_client(400, 'domains_invalid_mx');
        $result = $client->messages->create([]);
    }

    /**
     * @covers ::update
     */
    public function testUpdateAMessage()
    {
        $client = cm_mock_client(200, 'message_ok');
        $result = $client->messages->update([]);

        $this->assertEquals('message_ok', $result->status);
    }

    /**
     * @covers ::getPreviewUrl
     */
    public function testGetPreviewUrlReturnsUrl()
    {
        $client = cm_mock_client(200, 'message_ok');
        $res = $client->messages->getPreviewUrl('fakeid');
        $this->assertStringStartsWith('http', $res);
    }

    /**
     * @covers ::sendPreview
     */
    public function testSendingPreviewReturnsAValidResponse()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ::sendMessage
     * @todo: result testing?
     */
    public function testSendingAMessageReturnsAValidResponse()
    {
        $tpl = 'message_ok';
        $body = file_get_contents(__DIR__ . '/responses/' . $tpl . '.json');

        // Create a mock and queue two responses.
        $mock = new MockHandler([ new Response('200', [], $body) ]);
        $stack = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $stack->push($history);

        $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $stack]);
        $res = $client->messages->sendMessage('fakeid');

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $res);

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        // TODO: test post request?
        // Iterate over the requests and responses
        /*
        foreach ($container as $transaction) {
            echo $transaction['request']->getMethod();
            //> GET, HEAD
            if ($transaction['response']) {
                echo $transaction['response']->getStatusCode();
                //> 200, 200
            } elseif ($transaction['error']) {
                echo $transaction['error'];
                //> exception
            }
            var_dump($transaction['options']);
            //> dumps the request options of the sent request.
        }
         */
    }

    /**
     * @covers ::sendPreview
     * FIXME
     */
    /**
     public function testGetPreviewUrlError()
     {
         $tpl = 'empty';
         $res = $this->prepareCheck('getPreviewUrl', $tpl, 'fakeid', 400);
         $this->assertFalse($res);

         $this->markTestIncomplete(
             'This test has not been implemented yet.'
         );
     }
     */

}
