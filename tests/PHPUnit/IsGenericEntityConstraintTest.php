<?php
namespace Crunchmail\PHPUnit;

use Crunchmail\PHPUnit\TestCase;
use Crunchmail\Entities\GenericEntity;
use Crunchmail\Entities\MessageEntity;

/**
 * @covers Crunchmail\PHPUnit\IsGenericEntityConstraint
 * @coversDefaultClass Crunchmail\PHPUnit\IsGenericEntityConstraint
 */
class testIsGenericEntityConstraint extends Testcase
{
    public function setUp()
    {
        $this->constraint = new \Crunchmail\PHPUnit\IsGenericEntityConstraint();
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testInvalidGenericEntity()
    {
        $this->assertFalse($this->constraint->matches('fake'));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testGenericEntity()
    {
        $cli = $this->quickMock();
        $entity = new GenericEntity($cli->messages, new \stdClass);
        $this->assertTrue($this->constraint->matches($entity));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testMessageEntity()
    {
        $cli = $this->quickMock();
        $entity = new MessageEntity($cli->messages, new \stdClass);
        $this->assertTrue($this->constraint->matches($entity));
    }
}
