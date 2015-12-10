<?php
/**
 * Test class for Crunchmail\Entity\RecipientEntity
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Middleware;

require_once(__DIR__ . '/..//helpers/cm_mock.php');

/**
 * Test class
 *
 * @coversDefaultClass \Crunchmail\Mails
 */
class RecipientEntityTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
    }

}
