<?php
/**
 * Base test class
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

namespace Crunchmail\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

/**
 * Test class
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    private $container = [];
    private $bodyHistory = [];


    /**
     * Shortcut to get a quick mocked client
     *
     * @return \Crunchmail\Client
     */
    public function quickMock()
    {
        $handler = call_user_func_array([$this, 'mockHandler'],
            func_get_args());
        return $this->mockClient($handler);
    }

    /**
     * Create a mocked client
     *
     * @return void
     */
    public function mockClient($handler)
    {
        $client = new \Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);
        return $client;
    }

    /**
     * Create a mocked handler
     *
     * @return void
     */
    public function mockHandler()
    {
        $responses = [];
        $this->bodyHistory = [];

        foreach (func_get_args() as $r)
        {
            list($tpl, $code) = $r;

            $body = file_get_contents(__DIR__ . '/responses/' . $tpl .
                '.json');

            $this->bodyHistory[] = json_decode($body);

            $responses[] = new MockHandler([ new Response($code, [], $body) ]);
        }

        // Create a mock and queue responses.
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        $this->container = [];
        $history = Middleware::history($this->container);

        // Add the history middleware to the handler stack.
        $handler->push($history);

        return $handler;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getSentBody($n)
    {
        return $this->bodyHistory[$n];
    }

    public function getHistory()
    {
        return $this->container;
    }
}
