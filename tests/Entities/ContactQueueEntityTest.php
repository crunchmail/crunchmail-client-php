<?php
/**
 * Test class for Crunchmail\Entity\ContactQueueEntity
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Tests;

use Crunchmail;
use Crunchmail\Entities\ContactQueueEntity;
use Crunchmail\PHPUnit\TestCase;

/**
 * Test class
 *
 * @covers \Crunchmail\Entities\ContactQueueEntity
 * @coversDefaultClass \Crunchmail\Entities\ContactQueueEntity
 */
class ContactQueueEntityTest extends TestCase
{
    /**
     * @covers ::consume
     */
    public function testConsumeCallThePoperMethod()
    {
        $client  = $this->quickMock(['empty', '200']);
        $data    = $this->getStdTemplate('queue_ok');
        $queue = new ContactQueueEntity($client->queues->queues, $data);

        $queue->consume();

        $req = $this->getHistoryRequest(0);

        $this->assertEquals('POST', $req->getMethod());

        $this->assertStringEndsWith('/consume/', $req->getUri()->getPath());
    }
}
