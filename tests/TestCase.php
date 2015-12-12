<?php
/**
 * Base test class
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail\Client;

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
        $h = call_user_func_array([$this, 'mockHandler'], func_get_args());
        return $this->mockClient($h);
    }

    /**
     * Create a mocked client
     *
     * @return void
     */
    public function mockClient($handler)
    {
        $client = new Client(['base_uri' => '', 'handler' => $handler]);
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

        $dir = __DIR__ . '/responses/';

        foreach (func_get_args() as $r)
        {
            list($tpl, $code) = $r;

            $path = $dir . $tpl;

            // automatic json extension
            if (! preg_match('#\.[a-z]+$#', $tpl))
            {
                $path .= '.json';
                $body = file_get_contents($path);
                $this->bodyHistory[] = json_decode($body);
            }
            else
            {
                $body = file_get_contents($path);
                $this->bodyHistory[] = $body;
            }

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

    public function methodsProvider()
    {
        $data = [];
        foreach (Client::$methods as $m)
        {
            $data[] = [$m];
        }
        return $data;
    }

    public function getHistoryRequest($i)
    {
        $history = $this->getHistory();
        return $history[$i]['request'];
    }

    public function getHistoryContent($i, $decode = true)
    {
        $req = $this->getHistoryRequest($i);
        $content = $req->getBody()->getContents();
        return $decode ? json_decode($content) : $content;
    }

    public function assertCollection($e, $name = 'Generic')
    {
        $name = '\Crunchmail\Collections\\' . $name . 'Collection';
        $this->assertInstanceOf($name, $e);
    }

    public function assertResource($e, $name = 'Generic')
    {
        $name = '\Crunchmail\Resources\\' . $name . 'Resource';
        $this->assertInstanceOf($name, $e);
    }

    public function assertEntity($e, $name = 'Generic')
    {
        $name = '\Crunchmail\Entities\\' . $name . 'Entity';
        $this->assertInstanceOf($name, $e);
    }
}
