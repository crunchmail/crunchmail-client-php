<?php
/**
 * Test class for Crunchmail\Exception\ApiException
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once( __DIR__ . '/../helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Exception\ApiException
 */
class ApiExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox getDetail() returns the last exception
     *
     * @covers ::getDetail
     */
    public function testGetDetail()
    {
        try
        {
            $client = cm_mock_client([['message_error', '400']]);
            $client->messages->get('http://fake');
        }
        catch (Crunchmail\Exception\ApiException $e)
        {
            $err = $e->getDetail();
        }

        $this->assertInstanceOf('\stdClass', $err);
    }

    /**
     * @testdox toHtml() method returns a proper string and code (500)
     *
     * @covers ::toHtml
     * @covers ::getCode
     */
    public function testExceptionToHtmlFor500()
    {
        try
        {
            $client = cm_mock_client([['empty', '500']]);
            $client->messages->delete('http://fake');
        }
        catch (Crunchmail\Exception\ApiException $e)
        {
            $err  = $e->toHtml();
            $code = $e->getCode();
        }

        $this->assertInternalType('string', $err);
        $this->assertContains('500 Internal Server Error', $err);
        $this->assertEquals(500, $code);
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
            $client = cm_mock_client([['message_invalid', '400']]);
            $client->messages->post([]);
        }
        catch (Crunchmail\Exception\ApiException $e)
        {
            $code = $e->getCode();
            $err  = $e->toHtml();
        }

        $this->assertContains('sender_email', $err);
        $this->assertEquals(400, $code);
    }

    /**
     * @testdox Receiving an invalid error does not breaks formatting
     *
     * @group bug-2030
     */
    public function testReceivingAnInvalidError()
    {
        try
        {
            $client = cm_mock_client([['message_invalid', '400']]);
            $client->messages->post([]);
        }
        catch (Crunchmail\Exception\ApiException $e)
        {
            $err  = $e->toHtml(false);
        }

        $this->assertNotContains('sender_email', $err);
    }

}
