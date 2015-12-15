<?php
namespace Crunchmail\PHPUnit;

use Crunchmail\PHPUnit\TestCase;
use Crunchmail\Collections\GenericCollection;

/**
 * @covers Crunchmail\PHPUnit\IsGenericCollectionConstraint
 * @coversDefaultClass Crunchmail\PHPUnit\IsGenericCollectionConstraint
 */
class testIsGenericCollectionConstraint extends TestCase
{
    public function setUp()
    {
        $this->constraint = new \Crunchmail\PHPUnit\IsGenericCollectionConstraint();
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testInvalidGenericCollection()
    {
        $this->assertFalse($this->constraint->matches('fake'));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testGenericCollection()
    {
        $cli = $this->quickMock();
        $data = $this->getStdTemplate('messages');
        $collection = new GenericCollection($cli->messages, $data);
        $this->assertTrue($this->constraint->matches($collection));
    }
}
