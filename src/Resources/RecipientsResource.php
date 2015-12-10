<?php
/**
 * Recipients resource
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @copyright (C) 2015 Oasiswork
 * @license MIT
 *
 */

namespace Crunchmail\Resources;

/**
 * Recipients resource class
 */
class RecipientsResource extends GenericResource
{
    /**
     * Overwrite post for this resource
     *
     * @param mixed recipients, string or array
     * @return \Crunchmail\Entity\RecipientEntity
     */
    public function post($recipients)
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

        return parent::post($format);
    }
}
