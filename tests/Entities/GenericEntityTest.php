<?php
/**
 * Test class for Crunchmail\Entity\GenericEntity
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @todo test accessing resource on linkless entity (optout)
 */

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\GenericEntity
 * @coversDefaultClass \Crunchmail\Entities\GenericEntity
 */
class GenericEntityTest extends \Crunchmail\Tests\TestCase
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

    /* ---------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * A simple test to use as dependencie when needing a message entity
     *
     * @return \Crunchmail\Entities\MessageEntity
     */
    public function testRetrivingAnEntity()
    {
        $handler = $this->mockHandler(['message_ok', '200']);
        $client  = $this->mockClient($handler);
        $entity = $client->messages->get('https://fake');

        $this->assertInstanceOf('\Crunchmail\Entities\MessageEntity', $entity);
        return $entity;
    }

    /**
     * @covers ::__construct
     * @covers \Crunchmail\Resources\GenericResource::__call
     * @dataProvider entitiesProvider
     */
    public function testAllEntitesCanBeRetrieve($path, $entityName, $tpl)
    {
        $handler = $this->mockHandler([$tpl, '200']);
        $client  = $this->mockClient($handler);

        $entity  = $client->$path->get('https://fake');

        $this->assertInstanceOf('\Crunchmail\Entities\\'. $entityName .
            'Entity', $entity);
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
     * @covers ::toEntity
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
     * @covers ::toEntity
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
     * @covers ::toEntity
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
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     */
    public function testAccessingUnknowResourceThrowsAnExceptiion($entity)
    {
        $resource = $entity->stupidresource;
    }

}
