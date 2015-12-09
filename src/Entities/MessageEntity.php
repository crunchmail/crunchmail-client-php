<?php
/**
 * Messages subclass for Crunchmail API
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 *
 * @link https://github.com/crunchmail/crunchmail-client-php
 * @link http://docs.guzzlephp.org/en/latest/
 */
namespace Crunchmail\Entities;

/**
 * Crunchmail\Client subclass Messages
 */
class MessageEntity extends \Crunchmail\Entities\GenericEntity
{
    /**
     * Return a human readable status from int status
     *
     * @param int $status
     * @return string
     *
     * @todo translation system
     */
    public function readableStatus()
    {
        $status = $this->status;
        $match = [

            'message_ok'      => "En attente d'envoi",
            'message_issues'  => "Le message contient des erreurs",
            'sent'            => "Le message a été envoyé",
            'sending'         => "En cours d'envoi…"
        ];

        return isset($match[$status]) ? $match[$status] : $status;
    }

    /**
     * Sending message via crunchmail API
     *
     * @return mixed
     */
    public function send()
    {
        return $this->patch(['status' => 'sending']);
    }

    /**
     * Return the message preview url after an api request
     *
     * @param string $id Message url id
     * @return string
    private function getPreviewUrl()
    {
        return $this->_links->preview_send->href;
    }
     */

    /**
     * Send the preview for the message to the given recipients
     *
     * @param array $recipients list of recipients for the test
     * @return mixed
     * TODO: move to PreviewResource
    public function sendPreview($recipients)
    {
        $recipients = is_array($recipients) ? $recipients : [ $recipients ];

        $url = $this->getPreviewUrl();

        // sending the preview via crunchmail API
        return $this->post(['to' => implode(',', $recipients) ], $url);
    }
     */

    /**
     * Check if the givem message is valid, or throw an exception
     */
    protected function checkMessage()
    {
        if (!isset($this->body->status))
        {
            throw new \RuntimeException('Invalid message');
        }
    }

    /**
     * Return true if the message status is message_ok
     */
    public function hasIssue()
    {
        $this->checkMessage();
        return $this->status === 'message_issues';
    }

    /**
     * Return true if the message status is message_ok
     */
    public function isReady()
    {
        $this->checkMessage();
        return $this->status === 'message_ok';
    }

    /**
     * Return true if the message is being sent
     *
     * @param object $msg Message
     */
    public function isSending()
    {
        $this->checkMessage();
        return $this->status === 'sending';
    }

    /**
     * Return true if the message has been sent
     */
    public function hasBeenSent()
    {
        $this->checkMessage();
        return $this->status === 'sent';
    }
}
