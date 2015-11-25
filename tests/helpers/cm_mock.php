<?php
/**
 * Helpers for unit testing
 *
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
function cm_mock_client($code, $tpl=null)
{
    $body = '';
    if (!empty($tpl))
    {
        $body = file_get_contents(__DIR__ . '/../responses/' . $tpl . '.json');
    }

    // Create a mock and queue two responses.
    $mock = new MockHandler([ new Response($code, [], $body) ]);

    $handler = HandlerStack::create($mock);
    $client = new Crunchmail\Client(['base_uri' => '', 'handler' =>
        $handler]);

    return $client;
}

function cm_get_message($tpl, $code=200)
{
    $client = cm_mock_client($code, $tpl);
    return $client->retrieve('/fake');
}

