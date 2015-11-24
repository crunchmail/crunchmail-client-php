<?php
/**
 * TODO: Test Exception result for unexpected errors
 * TODO: Test Exception result for API errors
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Messages
 */
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
     * Helpers
     *
     * @TODO: helper factorize
     */
    private function prepareTestException($code)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([ new Response($code, ['X-Foo' => 'Bar']) ]);

        $handler = HandlerStack::create($mock);
        $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);

        return $client;
    }

    protected function prepareCheck($method, $tpl, $param, $code=200)
    {
        $body = file_get_contents(__DIR__ . '/responses/' . $tpl . '.json');

        $mock = new MockHandler([ new Response($code, [], $body) ]);
        $handler = HandlerStack::create($mock);

        $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);
        return $client->messages->$method($param);
    }

    /**
     * Test
     *
     */
    public function testGetPreviewUrlReturnsUrl()
    {
        $tpl = 'message_ok';
        $res = $this->prepareCheck('getPreviewUrl', $tpl, 'fakeid');
        $this->assertStringStartsWith('http', $res);
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
     */
    public function testSendingPreviewReturnsAValidResponse()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
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
