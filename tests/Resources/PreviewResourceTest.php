<?php
/**
 * Test class for Crunchmail\Resources\PreviewResource
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */

/**
 * Test class
 *
 * @covers \Crunchmail\Resources\PreviewResource
 * @coversDefaultClass \Crunchmail\Resources\PreviewResource
 */
class PreviewResourceTest extends \Crunchmail\Tests\TestCase
{
    /**
     * @covers ::send
     */
    public function testSendingPreviewReturnsAValidResponse()
    {
        $client = $this->quickMock(['message_ok', '200'], ['message_ok', '200']);

        $msg = $client->messages->get('https://fakeid');
        $res = $msg->preview->send('f@fake.fr');

        $history = $this->getHistory();

        $this->assertTrue($msg->isReady($res));

        // checking requests
        $this->assertCount(2, $history);

        // checking getPreviw request
        $reqUrl = $history[0]['request'];
        $this->assertEquals('GET', $reqUrl->getMethod());
        $this->assertEquals('https://fakeid', (string) $reqUrl->getUri());

        // checking sending preview request
        $reqSend = $history[1]['request'];
        $this->assertEquals('POST', $reqSend->getMethod());

        // check that the preview url has been used
        $this->assertStringEndsWith('preview_send/', (string)
            $reqSend->getUri());
    }

    /**
     * @covers \Crunchmail\Resources\GenericResource::__call
     *
     * @expectedException Crunchmail\Exception\ApiException
     * @expectedExceptionCode 405
     */
    public function testGetMethodIsDisabled()
    {
        $client = $this->quickMock(
            ['message_ok', '200'],
            ['method_get_forbidden', '405']
        );
        $msg = $client->messages->get('https://fakeid');
        $res = $msg->preview->get();
    }
}
