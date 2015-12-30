<?php
/**
 * Test class for Crunchmail\Resources\GenericResource
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail\PHPUnit\TestCase;

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

    public function entitiesProvider()
    {
        return [
            ['messages',    'Message',    'message_ok'],
            ['domains',     'Domain',     'domain_ok'],
            ['recipients',  'Recipient',  'mail_ok'],
            ['attachments', 'Attachment', 'attachment_ok'],
            ['stats',       'Generic',    'stat_ok'],
            // FIXME: need more values
            //['bounces',     'Generic',    'bounce_ok'],
            //['spam_detail',     'Generic',    'spam_detail_ok'],
            //['optouts',     'Generic',    'optout_ok'],
        ];
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
     * A simple test to use as dependencie when needing a message entity
     *
     * @covers ::__construct
     *
     * @return \Crunchmail\Entities\MessageEntity
     */
    public function testRetrivingAnEntity()
    {
        $handler = $this->mockHandler(['message_ok', '200']);
        $client  = $this->mockClient($handler);
        $entity = $client->messages->get('https://fake');

        $this->assertEntity('Message', $entity);
        return $entity;
    }

    /**
     * @covers ::__construct
     * @dataProvider entitiesProvider
     */
    public function testAllEntitesCanBeRetrieve($path, $entityName, $tpl)
    {
        $handler = $this->mockHandler([$tpl, '200']);
        $client  = $this->mockClient($handler);

        $entity  = $client->$path->get('https://fake');

        $this->assertEntity($entityName, $entity);
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

        $req = $this->getHistoryRequest(0);

        $this->assertEquals($uri, $req->getUri());
    }

    /**
     * @covers ::__call
     */
    public function testAddingAMessage()
    {
        $cli = $this->quickMock(['message_ok', '200']);
        $msg = $cli->messages->post([]);
        $this->assertEntity('Message', $msg);
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

        $req = $this->getHistoryRequest(1);

        $this->assertEquals($uri, $req->getUri());
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

        $content = $this->getHistoryContent(0);

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

        $content = $this->getHistoryContent(0, false);

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
        $this->assertEntity('Message', $res);
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
        $this->assertGenericCollection($res);
    }

    /**
     * @covers ::filter
     */
    public function testCollectionCanBeFiltered()
    {
        $cli = $this->quickMock(['messages', 200]);

        $collection = $cli->messages->filter(['search' => 'fake'])->get();
        $collection->current();

        $req = $this->getHistoryRequest(0);

        $this->assertEquals('search=fake', $req->getUri()->getQuery());
    }

    /**
     * @covers ::filter
     */
    public function testEmptyFilter()
    {
        $cli = $this->quickMock(['messages', 200]);

        $collection = $cli->messages->filter([])->get();
        $collection->current();

        $req = $this->getHistoryRequest(0);

        $this->assertEquals('', $req->getUri()->getQuery());
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
     * @testdox Method post() throws an exception on invalid domain
     *
     * @covers ::__call
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     */
    public function testCreateWithInvalidDomain()
    {
        $client = $this->quickMock(['domains_invalid_mx', '400']);
        $client->messages->post([]);
    }

    /**
     * @covers ::getPath
     */
    public function testGetPath()
    {
        $cli = $this->quickMock();
        $this->assertEquals('messages', $cli->messages->getPath());
    }

    /**
     * @covers ::getEntityClass
     */
    public function testGetClassReturnsSingular()
    {
        $cli = $this->quickMock();
        $this->assertEquals('fake', $cli->fakes->getEntityName());
    }

    /**
     * @covers ::getEntityClass
     */
    public function testGetClassReturnsIdenticalForSingularPath()
    {
        $cli = $this->quickMock();
        $this->assertEquals('fake', $cli->fake->getEntityName());
    }

    /**
     * @covers ::getEntityClass
     */
    public function testGetClassMapSpecialEntities()
    {
        $cli = $this->quickMock();
        $this->assertEquals('category', $cli->categories->getEntityName());
    }

    /**
     * @covers ::__get
     */
    public function testSubResourcesHaveAProperPath()
    {
        $cli = $this->quickMock(['messages', 200]);
        $cli->contacts->lists->get();

        $req = $this->getHistoryRequest(0);
        $this->assertEquals('contacts/lists', $req->getUri()->getPath());
    }

    /**
     * @covers ::__get
     */
    public function testSubResourcesReturnAResource()
    {
        $cli = $this->quickMock();
        $this->assertGenericResource($cli->contacts->lists);
    }
}
