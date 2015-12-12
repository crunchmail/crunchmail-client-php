<?php
/**
 * Preview resource
 *
 * @author    Yannick Huerre <dev@sheoak.fr>
 * @copyright 2015 (c) Oasiswork
 * @license   https://opensource.org/licenses/MIT MIT
 *
 */

namespace Crunchmail\Resources;

/**
 * Preview resource class
 */
class PreviewResource extends GenericResource
{
    /**
     * Send the preview to the given recipients.
     * You can only call it from a message entity
     *
     * @example $message->preview->send($email)
     *
     * @param array $recipients list of recipients for the test
     * @return Crunchmail\Entity\GenericEntity
     *
     * @fixme move somewhere else, resolve conflict
     */
    public function send($recipients)
    {
        $recipients = is_array($recipients) ? $recipients : [ $recipients ];

        // sending the preview via crunchmail API
        return $this->post(['to' => implode(',', $recipients) ]);
    }
}
