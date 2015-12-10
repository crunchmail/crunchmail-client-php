<?php
/**
 *
 * @author Yannick Huerre <dev@sheoak.fr>
 * @copyright (C) 2015 Oasiswork
 * @license MIT
 *
 */

namespace Crunchmail\Resources;

/**
 * Crunchmail\Client main class
 */
class PreviewResource extends GenericResource
{

    public function get()
    {
        throw new \Crunchmail\Exception\ApiException('Get is forbidden on
            preview resource');
    }

    /**
     * Send the preview for the message to the given recipients
     *
     * @param array $recipients list of recipients for the test
     * @return mixed
     */
    public function send($recipients)
    {
        $recipients = is_array($recipients) ? $recipients : [ $recipients ];

        // sending the preview via crunchmail API
        return $this->post(['to' => implode(',', $recipients) ]);
    }

}
