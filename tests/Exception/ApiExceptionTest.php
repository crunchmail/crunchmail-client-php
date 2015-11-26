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
            $client = cm_mock_client(404, 'message_error')->remove('/fake');
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
            $client = cm_mock_client(500);
            $client->remove('/fake');
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
     * @testdox toHtml() method returns a proper string and code (404)
     *
     * @covers ::toHtml
     * @covers ::getCode
     */
    public function testExceptionToHtmlFor404()
    {
        try
        {
            $client = cm_mock_client(404);
            $client->remove('/fake');
        }
        catch (Crunchmail\Exception\ApiException $e)
        {
            $err  = $e->toHtml();
            $code = $e->getCode();
        }

        $this->assertInternalType('string', $err);
        $this->assertContains('404 Not Found', $err);
        $this->assertEquals(404, $code);
    }

    /**
     * @testdox toHtml hides error key if asked to
     *
     * @covers ::formatResponseOutput
     */
    public function testToHtmlHidesErrorKey()
    {
        $this->markTestIncomplete('todo');

        //$this->assertNotContains('keyone', $out);
    }

    /**
     * @testdox Receiving an invalid error does not breaks formatting
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 400
     *
     * @group bug-2030
     */
    public function testReceivingAnInvalidError()
    {
        cm_mock_client(400, 'invalid_error')->create([]);
    }

}
