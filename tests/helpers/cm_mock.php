<?php
/**
 * Helpers for unit testing
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 */

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

/**
 * Return a mocked client
 *
 * @param int $code http status code
 * @param array $body body response
 * @return Crunchmail\Client
 */
function cm_mock_client($code, $tpl=['empty'])
{
    $tpl = is_array($tpl) ? $tpl : [$tpl];
    $responses = [];

    foreach ($tpl as $t)
    {
        $body = file_get_contents(__DIR__ . '/../responses/' . $t . '.json');
        $responses[] = new MockHandler([ new Response($code, [], $body) ]);
    }

    // Create a mock and queue responses.
    $mock = new MockHandler($responses);

    $handler = HandlerStack::create($mock);
    $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);

    return $client;
}

function cm_get_message($tpl, $code=200)
{
    $client = cm_mock_client($code, $tpl);
    return $client->retrieve('/fake');
}

