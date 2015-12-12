<?php
/**
 * Test class for Crunchmail\Resources\GenericResource
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

/**
 * Test class
 *
 * @covers \Crunchmail\Resources\GenericResource
 * @coversDefaultClass \Crunchmail\Resources\GenericResource
 */
class GenericResourceTest extends TestCase
{
    /**
     * Provider : methods that are sending data
     *
     * @retur array
     */
    public function sendingMethodsProvider()
    {
        return [
            ['put'],
            ['patch'],
            ['post']
        ];
    }

    /**
     * Accessing a relative url is forbidden on resources
     *
     * @covers ::__call
     * @covers ::request
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingRelativeUrlThrowsAnException()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $cli->messages->get('invalid');
    }

    /**
     * @covers ::__call
     * @covers ::request
     *
     * @expectedException \RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingAnUnknowMethodThrowsAnException()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $cli->messages->studidcall();
    }

    /**
     * Resource path can be override by an absolute url
     *
     * @covers ::__call
     * @covers ::request
     */
    public function testAccessingAbsoluteUrlMakeTheApiCall()
    {
        $uri = 'https://fake';

        $cli = $this->quickMock(['message_ok', '200']);
        $cli->messages->get($uri);

        $history = $this->getHistory();

        $this->assertEquals($uri, $history[0]['request']->getUri());
    }

    /**
     * When creating a sub-resource, we need te be able to force the url
     * of the new resource
     *
     * @covers ::__call
     * @covers ::request
     * @covers \Crunchmail\Client::createResource
     */
    public function testForcingAnUrlWorks()
    {
        $uri = 'https://fakeurl';

        $cli = $this->quickMock(
            ['message_ok', '200'],
            ['message_ok', '200']
        );
        $msg = $cli->messages->get('https://shouldnotbethisurl');
        $res = $cli->createResource('messages', $uri, $msg);

        $res = $res->get();

        $history = $this->getHistory();

        $this->assertEquals($uri, $history[1]['request']->getUri());
    }

    /**
     * Making sure the values we post are actually send to guzzle
     *
     * @covers ::__call
     * @covers ::request
     * @dataProvider sendingMethodsProvider
     */
    public function testSentValuesAreActuallySent($method)
    {
        $values = ['test' => '1'];

        $cli = $this->quickMock(['message_ok', '200']);
        $cli->messages->$method($values);

        $history = $this->getHistory();
        $content = $history[0]['request']->getBody()->getContents();
        $content = json_decode($content);

        $this->assertObjectHasAttribute('test', $content);
        $this->assertEquals($values['test'], $content->test);
    }

    /**
     * File upload require sending multipart data
     *
     * @covers ::__call
     * @covers ::request
     */
    public function testMultipartParameterWorksProperly()
    {
        $method = 'post';
        $values = [
            [
                'name'      => 'fieldname',
                'contents'  => 'fieldvalue'
            ]
        ];

        $cli = $this->quickMock(['message_ok', '200']);
        $cli->messages->$method($values, 'multipart');

        $history = $this->getHistory();
        $content = $history[0]['request']->getBody()->getContents();

        $this->assertContains('fieldname', $content);
        $this->assertContains('fieldvalue', $content);
    }

    /**
     * All resources should be able to return an entity object
     *
     * @covers ::__call
     * @covers ::request
     * @dataProvider methodsProvider
     */
    public function testAllMethodsReturnAnEntity($method)
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $res = $cli->messages->$method();

        // because our response is message_ok, we shoul always get
        // a message entity
        $this->assertEntity($res, 'Message');
    }

    /**
     * All resources should be able to return a collection
     *
     * @covers ::__call
     * @covers ::request
     * @dataProvider methodsProvider
     */
    public function testAllMethodsReturnACollection($method)
    {
        $cli = $this->quickMock(['messages', '200']);
        $res = $cli->messages->$method();

        // because our response is messages, we shoul always get
        // a collection
        $this->assertCollection($res);
    }

    /**
     * @covers ::filter
     */
    public function testCollectionCanBeFiltered()
    {
        $cli = $this->quickMock(['messages', 200]);

        $collection = $cli->messages->filter(['search' => 'fake'])->get();
        $collection->current();

        $history = $this->getHistory();
        $req = $history[0]['request'];

        $this->assertEquals('search=fake', $req->getUri()->getQuery());
    }

    /**
     * @covers ::page
     */
    public function testAccessPage()
    {
        $client = $this->quickMock(
            ['messages', 200]
        );
        $client->messages->page(2);
        $request = $this->getHistoryRequest(0);
        $query = $request->getUri()->getQuery();

        $this->assertEquals('page=2', $query);
    }

    /**
     * @covers ::page
     *
     * @expectedExceptionCode 0
     * @expectedException \RuntimeException
     */
    public function testAccessingAnInvalidPageThrowsAnException()
    {
        $client = $this->quickMock(
            ['messages', 200]
        );
        $client->messages->page('error');
    }
}
