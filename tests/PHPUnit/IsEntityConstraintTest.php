<?php
namespace Crunchmail\PHPUnit;

use Crunchmail\PHPUnit\TestCase;
use Crunchmail\Entities\GenericEntity;
use Crunchmail\Entities\MessageEntity;

/**
 * @covers Crunchmail\PHPUnit\IsEntityConstraint
 * @coversDefaultClass Crunchmail\PHPUnit\IsEntityConstraint
 */
class testIsEntityConstraint extends TestCase
{
    public function setUp()
    {
        $this->constraint = new \Crunchmail\PHPUnit\IsEntityConstraint('Message');
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testInvalidEntity()
    {
        $this->assertFalse($this->constraint->matches('invalid JSON'));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testGenericEntity()
    {
        $cli = $this->quickMock();
        $entity = new GenericEntity($cli->messages, new \stdClass);
        $this->assertFalse($this->constraint->matches($entity));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testValidEntity()
    {
        $cli = $this->quickMock();
        $entity = new MessageEntity($cli->messages, new \stdClass);
        $this->assertTrue($this->constraint->matches($entity));
    }
}
