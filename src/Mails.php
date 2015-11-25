<?php
/**
 * Mail subclass for Crunchmail API
 *
 * Usage:
 * $result = $Client->mails->push($url, $arrayEmails);
 * $result = $Client->mails->push($url, $stringEmail);
 *
 * @license MIT
 * @copyright (C) 2015 Oasis Work
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 */
namespace Crunchmail;

class Mails extends Client
{
    /**
     * Add one or several recipients to a message
     *
     * @param string $url message id
     * @param mixed recipients, string or array
     * @return stdClass Response with invalid recipients in failed property
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

        return $result;
    }
}
