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
     * @covers ::__toString
     */
    public function testDomainIsConvertedToString()
    {
        $cli = $this->quickMock(['domains_ok', 200]);
        $res = $cli->domains->get();

        foreach ($res->current() as $domain)
        {
            $this->assertEquals($domain->name, (string) $domain);
        }
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
     */
    public function testVerifyValidDomainReturnsTrue($tpl, $expected)
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
