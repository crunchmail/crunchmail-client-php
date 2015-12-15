<?php
namespace Crunchmail\PHPUnit;

use Crunchmail\PHPUnit\TestCase;
use Crunchmail\Resources\DomainsResource;

/**
 * @covers Crunchmail\PHPUnit\IsResourceConstraint
 * @coversDefaultClass Crunchmail\PHPUnit\IsResourceConstraint
 */
class testIsResourceConstraint extends TestCase
{
    private $constraint;

    public function setUp()
    {
        $this->constraint = new \Crunchmail\PHPUnit\IsResourceConstraint('Domains');
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testInvalidResource()
    {
        $this->assertFalse($this->constraint->matches('fake'));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testResource()
    {
        $cli = $this->quickMock();
        $this->assertFalse($this->constraint->matches($cli->generic));
    }

    /**
     * @covers ::__construct
     * @covers ::matches
     */
    public function testMessageResource()
    {
        $cli = $this->quickMock();
        $this->assertTrue($this->constraint->matches($cli->domains));
    }
}
