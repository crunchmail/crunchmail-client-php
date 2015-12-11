<?php
/**
 * Test class for Crunchmail\Resources\DomainsResources
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 *
 * @covers \Crunchmail\Resources\DomainsResource
 * @coversDefaultClass \Crunchmail\Resources\DomainsResource
 */
class DomainsTest extends \Crunchmail\Tests\TestCase
{

    /* -----------------------------------------------------------------------
     * Helpers
     * --------------------------------------------------------------------- */

    /**
     * Create a client and call requested method
     */
    protected function prepareCheck($method, $tpl, $domain='fake.com')
    {
        $client = $this->quickMock([$tpl, 200]);
        return $client->domains->$method($domain);
    }

    /* -----------------------------------------------------------------------
     * Providers
     * --------------------------------------------------------------------- */

    public function domainVerifyProvider()
    {
        return [
            ['domains_ok', true],
            ['domains_empty', false],
            ['domains_invalid_dkim', false],
            ['domains_invalid_mx', false]
        ];
    }

    public function searchProvider()
    {
        return [
            ['domains_ok', 1],
            ['domains_empty', 0]
        ];
    }

    /* -----------------------------------------------------------------------
     * Tests
     * --------------------------------------------------------------------- */

    /**
     * @covers ::verify
    *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testVerifyInternalServerError()
    {
        $client = $this->quickMock(['empty', 500]);
        $res = $client->domains->verify('fake.com');
    }

    /**
     * @covers ::verify
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testSearchInternalServerError()
    {
        $client = $this->quickMock(['empty', 500]);
        $res = $client->domains->search('fake.com');
    }

    /**
     * @testdox Searching a valid domain returns a valid collection
     *
     * @dataProvider searchProvider
     *
     * @covers ::verify
     * @covers \Crunchmail\Entities\DomainEntity::__toString
     */
    public function testSearchDomainReturnsACollection($tpl, $count)
    {
        $res = $this->prepareCheck('search', $tpl);

        $this->assertInstanceOf('\Crunchmail\Collections\GenericCollection',
            $res);

        $res = $res->current();
        $this->assertInternalType('array', $res);
        $this->assertCount($count, $res);

        foreach ($res as $domain)
        {
            $this->assertEquals($domain->name, (string) $domain);
        }
    }

    /**
     * @testdox Verifying a valid domain returns true
     *
     * @covers ::verify
     * @covers \Crunchmail\Entities\DomainEntity::verify
     * @covers \Crunchmail\Entities\DomainEntity::checkMx
     * @covers \Crunchmail\Entities\DomainEntity::checkDkim
     *
     * @dataProvider domainVerifyProvider
     *
     * @todo check call to post, with domain and email as parameter
     */
    public function testVerifyValidDomainReturnsTrue($tpl, $expected)
    {
        $res = $this->prepareCheck('verify', $tpl);

        if ($expected)
        {
            $this->assertTrue($res);
        }
        else
        {
            $this->assertFalse($res);
        }
    }
}
