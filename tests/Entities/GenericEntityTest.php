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
use Crunchmail\PHPUnit\TestCase;

use Crunchmail\Entities\GenericEntity;

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
     * @covers ::__construct
     */
    public function testRetrivingAnEntity()
    {
        $cli  = $this->quickMock();
        $data = $this->getStdTemplate('message_error');
        $entity = new GenericEntity($cli->messages, $data);

        $this->assertGenericEntity($entity);

        return $entity;
    }

    /**
     * @covers ::__construct
     * @covers ::__get
     * @covers ::getBody
     */
    public function testEntityFieldsCanBeAccessed()
    {
        $data   = $this->getStdTemplate('message_ok');
        $entity = new GenericEntity($this->quickMock()->messages, $data);

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
        $cli  = $this->quickMock(['message_ok', '200']);
        $data = $this->getStdTemplate('message_ok');
        $entity = new GenericEntity($cli->messages, $data);

        $refresh = $entity->get();

        $this->assertEquals($entity->getBody(), $refresh->getBody());
    }

    /**
     * @covers ::__call
     */
    public function testGetMethodActuallyRefreshTheEntity()
    {
        $cli  = $this->quickMock(['message_ok', '200']);
        $data = $this->getStdTemplate('message_error');
        $entity = new GenericEntity($cli->messages, $data);

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
        $cli    = $this->quickMock();
        $entity = new GenericEntity($cli->messages, new \stdClass());

        $entity->get();
    }

    /**
     * @covers ::__call
     * @dataProvider methodProvider
     */
    public function testAllMethodRequestTheProperMethod($method)
    {
        $cli = $this->quickMock(
            ['message_ok', 200]
        );

        $data = $this->getStdTemplate('message_ok');
        $msg  = new GenericEntity($cli->messages, $data);

        $msg->$method();

        $req = $this->getHistoryRequest(0);
        $this->assertEquals(strtoupper($method), (string) $req->getMethod());
        $this->assertEquals($msg->url, (string) $req->getUri());
    }

    /**
     * @depends testRetrivingAnEntity
     * @covers ::__toString
     */
    public function testCanBeConvertedToString($entity)
    {
        $this->assertEquals($entity->url, (string) $entity);
    }

    /**
     * @depends testRetrivingAnEntity
     * @covers ::__isset
     */
    public function testFieldsCanBeTestedWithIsset($entity)
    {
        $this->assertInternalType('boolean', isset($entity->url));
        $this->assertTrue(isset($entity->url));
        $this->assertFalse(isset($entity->donotexists));
    }

    /**
     * @depends testRetrivingAnEntity
     * @covers ::__unset
     *
     * @expectedExceptionCode 0
     * @expectedException RuntimeException
     */
    public function testThatUnsetIsForbidden($entity)
    {
        unset($entity->url);
    }

    /**
     * @covers ::__get
     */
    public function testThatNullFieldsCanBeRead()
    {
        $cli  = $this->quickMock();
        $data = new \stdClass();
        $data->field = null;
        $entity = new GenericEntity($cli->messages, $data);

        $this->assertNull($entity->field);
    }
}
