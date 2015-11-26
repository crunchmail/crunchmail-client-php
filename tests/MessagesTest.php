<?php
/**
 * Test class for Crunchmail\Messages
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
require_once('helpers/cm_mock.php');

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Messages
 */
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
     * @testdox Method create() works properly
     *
     * @covers ::create
     */
    public function testCreateAMessage()
    {
        $client = cm_mock_client(200, 'message_ok');
        $result = $client->messages->create([]);

        $this->assertEquals('message_ok', $result->status);
    }

    /**
     * @testdox Method update() works properly
     *
     * @covers ::update
     */
    public function testUpdateAMessage()
    {
        $client = cm_mock_client(200, 'message_ok');
        $result = $client->messages->update([]);

        $this->assertEquals('message_ok', $result->status);
    }

    /**
     * @testdox Method hasBeenSent() works properly
     *
     * @covers ::hasBeenSent
     * @covers ::checkMessage
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
     * @testdox Method isSending() works properly
     *
     * @covers ::isSending
     * @covers ::checkMessage
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
     * @testdox Method isReady() works properly
     *
     * @covers ::isReady
     * @covers ::checkMessage
     */
    public function testIsReady()
    {
        $msg = cm_get_message('message_ok');
        $this->assertTrue(Crunchmail\Messages::isReady($msg));
        $this->assertFalse(Crunchmail\Messages::isSending($msg));
        $this->assertFalse(Crunchmail\Messages::hasBeenSent($msg));
        $this->assertFalse(Crunchmail\Messages::hasIssue($msg));
    }

    /**
     * @testdox Method hasError() works properly
     *
     * @covers ::hasIssue
     * @covers ::checkMessage
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
     * @testdox Method hasBeenSent() throws an exception on invalid parameter
     *
     * @covers ::hasBeenSent
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testHasBeenSentThrowsAnExceptionOnInvalidParameter()
    {
        Crunchmail\Messages::hasBeenSent('error');
    }

    /**
     * @testdox Method isSending() throws an exception on invalid parameter
     *
     * @covers ::isSending
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testIsSendingThrowsAnExceptionOnInvalidParameter()
    {
        Crunchmail\Messages::isSending('error');
    }

    /**
     * @testdox Method isReady() throws an exception on invalid parameter
     *
     * @covers ::isReady
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testIsReadyThrowsAnExceptionOnInvalidParameter()
    {
        Crunchmail\Messages::isReady('error');
    }

    /**
     * @testdox Method hasIssue() throws an exception on invalid parameter
     *
     * @covers ::hasIssue
     * @covers ::checkMessage
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testMessageHasIssueThrowsAnException()
    {
        Crunchmail\Messages::hasIssue('error');
    }

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
        $client = cm_mock_client(200, ['message_ok', 'message_ok']);
        $res = $client->messages->sendPreview('fakeid', 'fakeemail@fake.fr');

        $this->assertTrue(\Crunchmail\Messages::isReady($res));
    }

    /**
     * @covers ::sendMessage
     */
    public function testSendingAMessageReturnsAValidResponse()
    {
        $tpl = 'message_sending';
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

        $this->assertInstanceOf('stdClass', $res);
        $this->assertTrue(\Crunchmail\Messages::isSending($res));

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
