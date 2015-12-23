<?php
/**
 * Base test class
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 *
 * @todo find a way to add request to an already created mocked client
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
    private $container   = [];
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
        $client = new Client([
            'base_uri'  => '',
            'token_uri' => '',
            'handler'   => $handler
        ]);
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

        // TODO: enhance this with middleware history?
        $this->bodyHistory = [];

        foreach (func_get_args() as $r)
        {
            list($tpl, $code) = $r;

            $body = $this->getTemplate($tpl);
            $this->bodyHistory[] = $body;

            $responses[] = new MockHandler([ new Response($code, [], $body) ]);
        }

        // Create a mock handler and queue responses.
        $handler = new MockHandler($responses);
        $stack   = HandlerStack::create($handler);

        // keep history
        $this->container = [];
        $history = Middleware::history($this->container);

        // Add the history middleware to the handler stack.
        $stack->push($history);

        return $stack;
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

    /* ---------------------------------------------------------------------
     * New assertions
     * --------------------------------------------------------------------- */

    // @codeCoverageIgnoreStart

    public static function assertGenericEntity($other)
    {
        self::assertThat($other, self::isGenericEntity());
    }

    public static function assertGenericResource($other)
    {
        self::assertThat($other, self::isGenericResource());
    }

    public static function assertGenericCollection($other)
    {
        self::assertThat($other, self::isGenericCollection());
    }

    public static function assertEntity($type, $other)
    {
        self::assertThat($other, self::isEntity($type));
    }

    public static function assertResource($type, $other)
    {
        self::assertThat($other, self::isResource($type));
    }

    /* ---------------------------------------------------------------------
     * New constraints
     * --------------------------------------------------------------------- */
    public static function isGenericResource()
    {
        return new \Crunchmail\PHPUnit\IsGenericResourceConstraint();
    }

    public static function isGenericCollection()
    {
        return new \Crunchmail\PHPUnit\IsGenericCollectionConstraint();
    }

    public static function isGenericEntity()
    {
        return new \Crunchmail\PHPUnit\IsGenericEntityConstraint();
    }

    public static function isEntity($type)
    {
        return new \Crunchmail\PHPUnit\IsEntityConstraint($type);
    }

    public static function isResource($type)
    {
        return new \Crunchmail\PHPUnit\IsResourceConstraint($type);
    }


    // @codeCoverageIgnoreEnd
}
