<?php
namespace Crunchmail\PHPUnit;

use Crunchmail\PHPUnit\TestCase;
use Crunchmail\Entities\GenericResource;
use Crunchmail\Entities\MessageResource;

/**
 * @covers Crunchmail\PHPUnit\IsGenericResourceConstraint
 * @coversDefaultClass Crunchmail\PHPUnit\IsGenericResourceConstraint
 */
class testIsGenericResourceConstraint extends TestCase
{
    public function setUp()
    {
        $this->constraint = new \Crunchmail\PHPUnit\IsGenericResourceConstraint();
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testInvalidGenericResource()
    {
        $this->assertFalse($this->constraint->matches('fake'));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testGenericResource()
    {
        $cli = $this->quickMock();
        $this->assertTrue($this->constraint->matches($cli->generic));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testMessageResource()
    {
        $cli = $this->quickMock();
        $this->assertTrue($this->constraint->matches($cli->messages));
    }
}
