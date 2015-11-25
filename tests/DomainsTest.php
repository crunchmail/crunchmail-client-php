<?php
/**
 * Test class for Crunchmail\Domains
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Domains
 * @fixme PHPUnit does not understand coversDefaultClass comment correctly
 */
require_once('helpers/cm_mock.php');

/**
 * Test class
 */
class DomainsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    /**
     * Helpers
     */

    /**
     * Create a client and call requested method
     */
    protected function prepareCheck($method, $tpl, $domain='fake.com')
    {
        $client = cm_mock_client(200, $tpl);
        return $client->domains->$method($domain);
    }

    /**
     * Test
     */

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     * @covers \Crunchmail\Domains::verify
     */
    public function testVerifyInternalServerError()
    {
        $client = cm_mock_client(500);
        $res = $client->domains->verify('fake.com');
    }

    /**
     * @covers Crunchmail\Domains::search
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 500
     */
    public function testSearchInternalServerError()
    {
        $client = cm_mock_client(500);
        $res = $client->domains->search('fake.com');
    }

    /**
     * Check searching a defined domain
     *
     * @covers \Crunchmail\Domains::search
     */
    public function testSearchDomainReturnsTrue()
    {
        $res = $this->prepareCheck('search', 'domains_ok');
        $this->assertTrue(is_array($res));
    }

    /**
     * Check searching an undefined domain
     *
     * @covers \Crunchmail\Domains::search
     */
    public function testSearchUnknowDomainReturnsFalse()
    {
        $res = $this->prepareCheck('search', 'domains_empty');

        $this->assertTrue(is_array($res));
        $this->assertTrue(count($res) === 0);
    }

    /**
     * Check testing a valid domain
     *
     * @covers \Crunchmail\Domains::verify
     * @todo check call to post, with domain and email as parameter
     */
    public function testVerifyValidDomainReturnsTrue()
    {
        $res = $this->prepareCheck('verify', 'domains_ok');
        $this->assertTrue($res);
    }

    /**
     * Check testing an invalid domain
     *
     * @covers \Crunchmail\Domains::verify
     */
    public function testVerifyInvalidDomainReturnsFalse()
    {
        $res = $this->prepareCheck('verify', 'domains_empty');
        $this->assertFalse($res);
    }

    /**
     * Check testing an existing but invalid domain (dkim error)
     *
     * @covers \Crunchmail\Domains::verify
     */
    public function testDomainInvalidDkim()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_dkim');
        $this->assertFalse($res);
    }

    /**
     * Check testing an existing but invalid domain (mx error)
     *
     * @covers \Crunchmail\Domains::verify
     */
    public function testDomainInvalidMx()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_mx');

        $this->assertFalse($res);
    }

    /**
     * Check testing an existing but invalid domain (mx error)
     *
     * @covers \Crunchmail\Domains::verify
     */
    public function testVerifyEmptyDomainReturnFalse()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_mx', '');
        $this->assertFalse($res);
    }
}
