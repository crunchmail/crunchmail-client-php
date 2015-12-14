<?php
/**
 * Preview Send resource
 *
 * This resource is not a classic resource in the API, but it is easier
 * to handle it this way as it simplifies the code.
 *
 * It allows to send the preview easily form a message entitiy:
 * $message->preview_send->post($values);
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Crunchmail\Resources;

/**
 * Preview resource class
 */
class PreviewSendResource extends GenericResource
{
    public function send($recipients)
    {
        $recipients = is_array($recipients) ? $recipients : [ $recipients ];
        $values = ['to' => implode(',', $recipients) ];

        // sending the preview via crunchmail API
        return $this->post($values);
    }
}
