<?php
/**
 * Test class for Crunchmail\Entity\GenericEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 *
 * @todo test accessing resource on linkless entity (optout)
 */

namespace Crunchmail\Tests;

use Crunchmail;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\GenericEntity
 * @coversDefaultClass \Crunchmail\Entities\GenericEntity
 */
class GenericEntityTest extends TestCase
{
    /* ---------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */

    public function entitiesProvider()
    {
        return [
            ['messages',    'Message',    'message_ok'],
            ['attachments', 'Attachment', 'attachment_ok']
        ];
    }

    public function methodProvider()
    {
        return [
            ['get'],
            ['post'],
            ['put'],
            ['patch']
        ];
    }

    /* ---------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

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

        $this->assertEntity($entity, 'Message');
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

        $this->assertEntity($entity, $entityName);
    }

    /**
     * @depends testRetrivingAnEntity
     * @covers ::__construct
     * @covers ::__get
     * @covers ::getBody
     */
    public function testEntityFieldsCanBeAccessed($entity)
    {
        $this->assertInstanceOf('stdClass', $entity->getBody());

        foreach ((array) $entity->getBody() as $k => $v)
        {
            $this->assertSame($entity->$k, $v);
        }
    }

    /**
     * @covers ::__call
     */
    public function testGetMethodsReturnsTheSameEntity()
    {
        $handler = $this->mockHandler(
            ['message_ok', '200'],
            ['message_ok', '200']
        );
        $client  = $this->mockClient($handler);

        $entity = $client->messages->get('https://fake');
        $refresh = $entity->get();

        $this->assertEquals($entity, $refresh);
    }

    /**
     * @covers ::__call
     */
    public function testGetMethodActuallyRefreshTheEntity()
    {
        $handler = $this->mockHandler(
            ['message_ok', '200'],
            ['message_error', '200']
        );
        $client  = $this->mockClient($handler);

        $entity = $client->messages->get('https://fake');
        $refresh = $entity->get();

        $this->assertNotEquals($entity, $refresh);
    }

    /**
     * @depends testRetrivingAnEntity
     * @covers ::__call
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingAnInvalidMethodThrowsAnException($entity)
    {
        $entity->stupidCall();
    }

    /**
     * @depends testRetrivingAnEntity
     * @covers ::__construct
     * @covers ::__get
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingUnknowResourceThrowsAnExceptiion($entity)
    {
        $entity->stupidresource;
    }

    /**
     * @covers ::__construct
     * @covers ::__call
     *
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingAnEmptyEntityMethodThrowsAnException()
    {
        $handler = $this->mockHandler(['message_ok', '200']);
        $client  = $this->mockClient($handler);
        $entity = $client->messages->get('https://fake');

        $entity->get();
    }

    /**
     * @covers ::__call
     * @dataProvider methodProvider
     */
    public function testAllMethodSendTheProperMethod($method)
    {
        $cli = $this->quickMock(
            ['message_ok', 200],
            ['message_ok', 200]
        );
        $msg = $cli->messages->get('https://fake');

        $msg->$method();

        $req = $this->getHistoryRequest(1);
        $this->assertEquals(strtoupper($method), (string) $req->getMethod());
        $this->assertEquals($msg->url, (string) $req->getUri());
    }
}
