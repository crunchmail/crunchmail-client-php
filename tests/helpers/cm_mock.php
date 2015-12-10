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
use GuzzleHttp\Middleware;


/**
 * Return a mocked client
 *
 * @param int $code http status code
 * @param array $body body response
 * @return Crunchmail\Client
 */
function cm_mock_client($send=[], &$container=null)
{
    $responses = [];

    foreach ($send as $params)
    {
        list($tpl, $code) = $params;

        $body = file_get_contents(__DIR__ . '/../responses/' . $tpl . '.json');
        $responses[] = new MockHandler([ new Response($code, [], $body) ]);
    }

    // Create a mock and queue responses.
    $mock = new MockHandler($responses);

    $handler = HandlerStack::create($mock);

    // keep history of requests
    if (isset($container))
    {
        $container = [];
        $history = Middleware::history($container);

        // Add the history middleware to the handler stack.
        $handler->push($history);
    }

    $client = new Crunchmail\Client(['base_uri' => '', 'handler' => $handler]);

    return $client;
}

function cm_get_message($send)
{
    $client = cm_mock_client($send);
    return $client->messages->get('https://fake');
}

