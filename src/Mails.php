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

        $recipients = is_array($recipients) ? $recipients : [$recipients];

        // format recipients for the API POST, waiting for an associative array
        // with to/message keys
        foreach ($recipients as $mail)
        {
            $format[] = [
                'to'        => $mail,
                'message'   => $url
                ];
        }

        // adding recipients on the API
        $result = $this->create($format);

        $this->invalidRecipients = array();

        // some invalid recipients?
        if (isset($result->failed))
        {
            foreach ($result->failed as $mail => $err)
            {
                $this->invalidRecipients[] = $mail;
            }
        }
    }
}
