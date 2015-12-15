<?php
/**
 * Base test class
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\PHPUnit;

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
        $handler = call_user_func_array([$this, 'mockHandler'], func_get_args());
        return $this->mockClient($handler);
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
     *
     * @SuppressWarnings(PHPMD)
     */
    public function mockHandler()
    {
        $responses = [];
        $this->bodyHistory = [];

        foreach (func_get_args() as $r)
        {
            list($tpl, $code) = $r;

            $body = $this->getTemplate($tpl);
            $this->bodyHistory[] = $body;

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

    public function getTemplate($tpl)
    {
        $dir = __DIR__ . '/../../tests/responses/';
        $path = $dir . $tpl;

        // automatic json extension
        if (! preg_match('#\.[a-z]+$#', $tpl))
        {
            $path .= '.json';
        }
        return file_get_contents($path);
    }

    public function getStdTemplate($tpl)
    {
        return json_decode($this->getTemplate($tpl));
    }

    /**
     * Return the $ind body sent in the history
     *
     * @param int $ind indice
     *
     * @return void
     */
    public function getSentBody($ind)
    {
        return json_decode($this->bodyHistory[$ind]);
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

    public function getHistoryRequest($ind)
    {
        $history = $this->getHistory();
        return $history[$ind]['request'];
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function getHistoryContent($ind, $decode = true)
    {
        $req = $this->getHistoryRequest($ind);
        $content = $req->getBody()->getContents();
        return $decode ? json_decode($content) : $content;
    }

    public function assertCollection($ent, $name = 'Generic')
    {
        $name = '\Crunchmail\Collections\\' . $name . 'Collection';
        $this->assertInstanceOf($name, $ent);
    }

    public function assertResource($ent, $name = 'Generic')
    {
        $name = '\Crunchmail\Resources\\' . $name . 'Resource';
        $this->assertInstanceOf($name, $ent);
    }

    public static function assertGenericEntity($other)
    {
        self::assertThat($other, self::isGenericEntity());
    }

    public static function assertEntity($type, $other)
    {
        self::assertThat($other, self::isEntity($type));
    }

    public static function isGenericEntity()
    {
        return new \Crunchmail\PHPUnit\IsGenericEntityConstraint();
    }

    public static function isEntity($type)
    {
        return new \Crunchmail\PHPUnit\IsEntityConstraint($type);
    }
}
