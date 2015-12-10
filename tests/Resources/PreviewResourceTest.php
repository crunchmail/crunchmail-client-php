<?php
/**
 * Test class for Crunchmail\Resources\PreviewResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

require_once(__DIR__ . '/../helpers/cm_mock.php');

/**
 * Test class
 */
class PreviewResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     */
    public function testSendingPreviewReturnsAValidResponse()
    {
        $container = [];
        $client = cm_mock_client([['message_ok', '200'], ['message_ok', '200']],
            $container);

        $msg = $client->messages->get('https://fakeid');
        $res = $msg->preview->send('f@fake.fr');

        $this->assertTrue($msg->isReady($res));

        // checking requests
        $this->assertEquals(2, count($container));

        // checking getPreviw request
        $reqUrl = $container[0]['request'];
        $this->assertEquals('GET', $reqUrl->getMethod());
        $this->assertEquals('https://fakeid', (string) $reqUrl->getUri());

        // checking sending preview request
        $reqSend = $container[1]['request'];
        $this->assertEquals('POST', $reqSend->getMethod());

        // check that the preview url has been used
        $this->assertStringEndsWith('preview_send/', (string)
            $reqSend->getUri());

    }

    /**
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 0
     */
    public function testGetMethodIsDisabled()
    {
        $client = cm_mock_client([['message_ok', '200']]);
        $msg = $client->messages->get('https://fakeid');
        $res = $msg->preview->get();
    }
}
