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

}
