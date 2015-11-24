<?php
/**
 * TODO: Test Exception result for unexpected errors
 * TODO: Test Exception result for API errors
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @coversDefaultClass \Crunchmail\Domains
 */
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class DomainsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    /**
     * Helpers
     *
     * @TODO: helper factorize
     */
    private function prepareTestException($code, $name)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([ new Response($code, ['X-Foo' => 'Bar']) ]);

        $handler = HandlerStack::create($mock);
        $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);

        $this->setExpectedException($name);

        return $client;
    }

    protected function prepareCheck($method, $tpl, $domain='fake.com')
    {
        $body = file_get_contents(__DIR__ . '/responses/' . $tpl . '.json');

        $mock = new MockHandler([ new Response(200, [], $body) ]);
        $handler = HandlerStack::create($mock);

        $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);
        return $client->domains->$method($domain);
    }

    /**
     * Test
     */

    /**
     * @covers ::verify
     */
    public function testVerifyInternalServerError()
    {
        $client = $this->prepareTestException(500, 'Crunchmail\Exception\ApiException');
        $res = $client->domains->verify('fake.com');
    }

    /**
     * @covers ::search
     */
    public function testSearchInternalServerError()
    {
        $client = $this->prepareTestException(500, 'Crunchmail\Exception\ApiException');
        $res = $client->domains->search('fake.com');
    }

    /**
     * Check searching a defined domain
     *
     * @covers ::search
     */
    public function testSearchDomainReturnsTrue()
    {
        $res = $this->prepareCheck('search', 'domains_ok');
        $this->assertTrue(is_array($res));
    }

    /**
     * Check searching an undefined domain
     *
     * @covers ::search
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
     * @covers ::verify
     */
    public function testVerifyValidDomainReturnsTrue()
    {
        $res = $this->prepareCheck('verify', 'domains_ok');
        $this->assertTrue($res);
    }

    /**
     * Check testing an invalid domain
     *
     * @covers ::verify
     */
    public function testVerifyInvalidDomainReturnsFalse()
    {
        $res = $this->prepareCheck('verify', 'domains_empty');
        $this->assertFalse($res);
    }

    /**
     * Check testing an existing but invalid domain (dkim error)
     *
     * @covers ::verify
     */
    public function testDomainInvalidDkim()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_dkim');
        $this->assertFalse($res);
    }

    /**
     * Check testing an existing but invalid domain (mx error)
     *
     * @covers ::verify
     */
    public function testDomainInvalidMx()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_mx');

        $this->assertFalse($res);
    }

    /**
     * Check testing an existing but invalid domain (mx error)
     *
     * @covers ::verify
     */
    public function testVerifyEmptyDomainReturnFalse()
    {
        $res = $this->prepareCheck('verify', 'domains_invalid_mx', '');
        $this->assertFalse($res);
    }
}
