<?php
/**
 * Message entity
 *
 * @license MIT
 * @copyright (C) 2015 Oasiswork
 * @author Yannick Huerre <dev@sheoak.fr>
 */
namespace Crunchmail\Entities;

/**
 * Message entity class
 */
class MessageEntity extends \Crunchmail\Entities\GenericEntity
{
    public function __toString()
    {
        return $this->body->name;
    }

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
     * Return true if the message status is message_ok
     */
    public function hasIssue()
    {
        return $this->status === 'message_issues';
    }

    /**
     * Return true if the message status is message_ok
     */
    public function isReady()
    {
        return $this->status === 'message_ok';
    }

    /**
     * Return true if the message is being sent
     *
     * @param object $msg Message
     */
    public function isSending()
    {
        return $this->status === 'sending';
    }

    /**
     * Return true if the message has been sent
     */
    public function hasBeenSent()
    {
        return $this->status === 'sent';
    }

    /**
     * Retrieve html content
     *
     * @return string
     */
    public function html()
    {
        return $this->getMsgContent('preview.html');
    }

    /**
     * Retrieve text content
     *
     * @return string
     */
    public function txt()
    {
        return $this->getMsgContent('preview.txt');
    }

    /**
     * Retrieve url content as a string
     *
     * @return GuzzleHttp\Psr7\Response
     */
    private function getMsgContent($key)
    {
        $url = $this->_links->$key->href;
        $body = $this->client->get($url);
        return (string) $body->getBody();
    }
}
