<?php
/**
 * Mail ressources for Crunchmail API
 *
 * Usage:
 * $Client->Mails->push($url, $listOfEmails);
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 */

namespace Crunchmail;

// crunchmail dependencies
use Crunchmail\Exception\ApiException;
use Crunchmail\Exception\ApiMailsException;

// guzzle dependencies
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class Mails extends Client
{

    /**
     * Add recipients to a message
     * Handle errors as custom exceptions
     *
     * @todo Simplify, avoid imbricated foreach
     */
    public function push($url, $recipients)
    {
        // modify post, adding base_uri as 'message' key
        $format = [];

        // format recipients for the API POST, waiting for an associative array
        // with to/message keys
        foreach ($recipients as $mail)
        {
            $format[] = [
                'to'        => $mail,
                'message'   => $url
                ];
        }

        try
        {
            // adding recipients on the API
            return $this->post('', ['json' => $format]);
        }
        catch (ClientException $e)
        {
            // Client Exception most likely means some emails where invalid
            // and we need to tell the customer which one(s)
            $emailErrors = array();

            // TODO: simplify algo complexity
            if ($e->hasResponse())
            {
                // get the response body as an array
                $Response = $e->getResponse();
                $body = json_decode($Response->getBody());
                $msg = !empty($body) ? $body : [];

                // generate the list of invalid emails: we need to match it
                // with the list of sended emails, as the API only returns
                // the indexes, not the emails
                $out = "";
                foreach ($msg as $k => $v)
                {
                    // no error for this mail index
                    if (empty($v->to))
                        continue;

                    // error as a string
                    $out .= '<p>' . $recipients[$k] . ' : ';

                    foreach ($v as $str)
                    {
                        // force array
                        $str = is_array($str) ? $str : [ $str ];

                        // keep track of invalid emails
                        $emailErrors[] = $recipients[$k];

                        if (!empty($str))
                        {
                            foreach ($str as $s)
                            {
                                $out .=  $s . "<br>";
                            }
                        }
                    }
                    $out .= '</p>';
                }

                $msg = $out;
            }

            self::handleGuzzleException($e, $msg, 'ApiMailsException', $emailErrors);
        }
        catch (RequestException $e)
        {
            // never happens?
            self::handleGuzzleException($e, '', 'ApiMailsException', []);
        }
    }
}
