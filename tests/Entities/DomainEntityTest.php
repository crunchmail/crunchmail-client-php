<?php
/**
 * Test class for Crunchmail\Entity\DomainEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;
use Crunchmail\Entities\DomainEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\DomainEntity
 * @coversDefaultClass \Crunchmail\Entities\DomainEntity
 */
class DomainEntityTest extends TestCase
{
    public function domainVerifyProvider()
    {
        return [
            ['domains_ok', true],
            ['domains_invalid_dkim', false],
            ['domains_invalid_mx', false]
        ];
    }

    /**
     * @covers ::__construct
     */
    public function testDomainCanBeCreated()
    {
        $data = $this->getStdTemplate('domain_ok');
        $cli  = $this->quickMock();
        $entity = new DomainEntity($cli->domains, $data);

        $this->assertEntity('Domain', $entity);

        return $entity;
    }

    /**
     * @depends testDomainCanBeCreated
     *
     * @covers ::__toString
     */
    public function testDomainIsConvertedToString($entity)
    {
        $this->assertEquals($entity->name, (string) $entity);
    }

    /**
     * @testdox Verifying a valid/invalid domain returns true/false
     *
     * @covers ::verify
     * @covers ::checkMx
     * @covers ::checkDkim
     *
     * @dataProvider domainVerifyProvider
     *
     * @todo check call to post, with domain and email as parameter
     * @todo use single entites templates
     */
    public function testVerifyValidDomainReturnsProperResult($tpl, $expected)
    {
        $cli = $this->quickMock([$tpl, 200]);
        $list = $cli->domains->get();

        // check all domains of the collection
        foreach ($list->current() as $domain)
        {
            $res = $domain->verify();
            if ($expected)
            {
                return $this->assertTrue($res);
            }
            $this->assertFalse($res);
        }
    }
}
