<?php
/**
 * Test class for Crunchmail\Resources\DomainsResources
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @todo verify request parameters (filter)
 */

/**
 * Test class
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
     * Test
     * --------------------------------------------------------------------- */

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testVerifyInternalServerError()
    {
        $client = $this->quickMock(['empty', 500]);
        $res = $client->domains->verify('fake.com');
    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testSearchInternalServerError()
    {
        $client = $this->quickMock(['empty', 500]);
        $res = $client->domains->search('fake.com');
    }

    /**
     * @testdox Searching a valid domain returns an array
     */
    public function testSearchDomainReturnsACollection()
    {
        $res = $this->prepareCheck('search', 'domains_ok');
        $this->assertInstanceOf('\Crunchmail\Collections\GenericCollection', $res);
    }

    /**
     * @testdox Searching a invalid domain returns an empty array
     */
    public function testSearchUnknowDomainReturnsAnEmptyArray()
    {
        $res = $this->prepareCheck('search', 'domains_empty');
        $res = $res->current();

        $this->assertTrue(is_array($res));
        $this->assertTrue(count($res) === 0);
    }

    /**
     * @testdox Verifying a valid domain returns true
     *
     * @todo check call to post, with domain and email as parameter
     */
    public function testVerifyValidDomainReturnsTrue()
    {
        $res = $this->prepareCheck('verify', 'domains_ok');
        $this->assertTrue($res);
    }

    /**
     * @testdox Verifying an unknow domain returns false
     */
    public function testVerifyInvalidDomainReturnsFalse()
    {
        $res = $this->prepareCheck('verify', 'domains_empty');
        $this->assertFalse($res);
    }

    /**
     * @testdox Verifying an invalid existing domain returns false (dkim)
     */
    public function testDomainInvalidDkim()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_dkim');
        $this->assertFalse($res);
    }

    /**
     * @testdox Verifying an invalid existing domain returns false (mx)
     */
    public function testDomainInvalidMx()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_mx');
        $this->assertFalse($res);
    }
}
