<?php
/**
 * Test class for Crunchmail\Exception\ApiException
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use \Crunchmail\Exception\ApiException;

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Exception\ApiException
 */
class ApiExceptionTest extends TestCase
{
    /**
     * @testdox getDetail() returns the last exception
     *
     * @covers ::getDetail
     * @todo test error format
     */
    public function testGetDetail()
    {
        try
        {
            $client = $this->quickMock(['message_error', 400]);
            $client->messages->get('http://fake');
        }
        catch (ApiException $e)
        {
            $err = $e->getDetail();
        }

        $this->assertInstanceOf('\stdClass', $err);

        return $e;
    }

    /**
     * @testdox toHtml() method returns a proper string and code (500)
     *
     * @covers ::toHtml
     * @covers ::getCode
     *
     */
    public function testExceptionToHtmlFor500()
    {
        try
        {
            $client = $this->quickMock(['empty', 500]);
            $client->messages->delete('http://fake');
        }
        catch (ApiException $e)
        {
            $err  = $e->toHtml();
            $code = $e->getCode();
        }

        $this->assertInternalType('string', $err);
        $this->assertContains('500 Internal Server Error', $err);
        $this->assertEquals(500, $code);
    }

    /**
     * @testdox toHtml() method can format domain verify errors
     *
     * @covers ::toHtml
     */
    public function testDomainErrorFormatting()
    {
        try
        {
            $cli = $this->quickMock(
                ['message_ok', '200'],
                ['domain_error', 500]
            );
            $msg = $cli->messages->get('http://fake');
            $msg->post([]);
        }
        catch (ApiException $e)
        {
            $this->assertInternalType('string', $e->toHtml());
            $this->assertEquals(500, $e->getCode());
        }
    }

    /**
     * @testdox toHtml() method returns a proper string and code (400)
     *
     * @covers ::toHtml
     * @covers ::getCode
     */
    public function testExceptionToHtmlFor400()
    {
        try
        {
            $client = $this->quickMock(['message_invalid', 400]);
            $client->messages->post([]);
        }
        catch (ApiException $e)
        {
            $code = $e->getCode();
            $err  = $e->toHtml();
        }

        $this->assertContains('sender_email', $err);
        $this->assertEquals(400, $code);
    }

    /**
     * @depends testGetDetail
     */
    public function testExceptionIsConvertedToString($exception)
    {
        $this->assertContains('400', (string) $exception);
    }
}
